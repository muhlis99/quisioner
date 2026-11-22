<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\JatahMakul;
use App\Models\Mahasiswa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SiakadSyncService
{
    /**
     * Menarik seluruh data mahasiswa dari API SIAKAD (`/mahasiswa/all`) lalu menyinkronkannya
     * ke tabel tb_mahasiswa (kolom: id, nim, nama) tanpa mengubah struktur tabel database.
     *
     * @return array{status: string, total_pages: int, total_api: int, processed: int, inserted: int, updated: int, unchanged: int, skipped: int}
     */
    public function syncMahasiswa(): array
    {
        $fetched = $this->fetchAllMahasiswa();
        $result = $this->syncRecords($fetched['records']);

        return array_merge(['total_pages' => $fetched['total_pages']], $result);
    }

    /**
     * Menarik seluruh halaman data mahasiswa dari API SIAKAD tanpa menulis ke database.
     *
     * @return array{total_pages: int, total_reported: int, records: array<int, mixed>}
     */
    public function fetchAllMahasiswa(): array
    {
        return $this->fetchAllPaginated('/mahasiswa/all', 'mahasiswa');
    }

    /**
     * Menarik seluruh data dosen dari API SIAKAD (`/dosen/all`) lalu menyinkronkannya
     * ke tabel tb_dosen (kolom: id, nipy, nama) tanpa mengubah struktur tabel database.
     *
     * @return array{status: string, total_pages: int, total_api: int, processed: int, inserted: int, updated: int, unchanged: int, skipped: int}
     */
    public function syncDosen(): array
    {
        $fetched = $this->fetchAllDosen();
        $result = $this->syncDosenRecords($fetched['records']);

        return array_merge(['total_pages' => $fetched['total_pages']], $result);
    }

    /**
     * Menarik seluruh halaman data dosen dari API SIAKAD tanpa menulis ke database.
     *
     * @return array{total_pages: int, total_reported: int, records: array<int, mixed>}
     */
    public function fetchAllDosen(): array
    {
        return $this->fetchAllPaginated('/dosen/all', 'dosen');
    }

    /**
     * Menarik jadwal kuliah seorang mahasiswa dari API SIAMDOS
     * (`/jadwalKuliah/getJadwalMahasiswa/{nim}`) lalu menormalisasi daftar mata kuliah & dosen.
     *
     * @return array{
     *     identitas: array<string, string>,
     *     parsed_tahun_ajaran: string,
     *     parsed_semester: string,
     *     items: list<array{kode_makul: string, nama_makul: string, nipy: string, nama_dosen: string}>
     * }
     */
    public function fetchJadwalMahasiswa(string $nim): array
    {
        $nimClean = trim($nim);
        $baseUrl = rtrim((string) config('services.siamdos.url', 'https://apisiamdos.ponpesnaa.net/v1'), '/');
        $endpoint = $baseUrl . '/jadwalKuliah/getJadwalMahasiswa/' . rawurlencode($nimClean);

        $response = $this->executeSiamdosGet($endpoint);
        $payload = $this->parseJsonResponse($response, $endpoint);

        return $this->normalizeSiamdosPayload($payload, $nimClean);
    }

    /**
     * Menarik & menyimpan jadwal kuliah untuk sekumpulan NIM mahasiswa secara paralel (batch)
     * menggunakan DB::transaction() + updateOrCreate anti-duplikat.
     *
     * @param  list<string>  $nims
     * @return array{
     *     processed_students: int,
     *     students_with_schedule: int,
     *     students_empty: int,
     *     students_failed: int,
     *     inserted: int,
     *     updated: int
     * }
     */
    public function syncJadwalMahasiswaBatch(array $nims, string $tahunAjaran, string $semester): array
    {
        $baseUrl = rtrim((string) config('services.siamdos.url', 'https://apisiamdos.ponpesnaa.net/v1'), '/');
        $dosenByNipy = Dosen::query()->pluck('nama', 'nipy')->all();
        $nipyByNamaLower = [];
        foreach ($dosenByNipy as $nipy => $nama) {
            $nipyByNamaLower[strtolower(trim((string) $nama))] = trim((string) $nipy);
        }

        $processedStudents = 0;
        $studentsWithSchedule = 0;
        $studentsEmpty = 0;
        $studentsFailed = 0;
        $rowsToSync = [];

        foreach (array_chunk($nims, 5) as $nimChunk) {
            $responses = Http::pool(function (Pool $pool) use ($nimChunk, $baseUrl) {
                $reqs = [];
                foreach ($nimChunk as $nim) {
                    $endpoint = $baseUrl . '/jadwalKuliah/getJadwalMahasiswa/' . rawurlencode($nim);
                    $reqs[$nim] = $this->configureSiamdosRequest($pool->as($nim))->get($endpoint);
                }

                return $reqs;
            });

            foreach ($nimChunk as $nim) {
                $processedStudents++;
                $res = $responses[$nim] ?? null;

                if (! $res instanceof Response || $res->failed()) {
                    $studentsFailed++;
                    Log::error('Generate massal jadwal SIAMDOS gagal untuk NIM.', [
                        'nim' => $nim,
                        'status' => $res instanceof Response ? $res->status() : null,
                        'error' => $res instanceof Throwable ? $res->getMessage() : null,
                    ]);
                    continue;
                }

                $payload = $res->json();
                if (! is_array($payload)) {
                    $studentsFailed++;
                    continue;
                }

                $normalized = $this->normalizeSiamdosPayload($payload, $nim, $dosenByNipy, $nipyByNamaLower);
                if (empty($normalized['items'])) {
                    $studentsEmpty++;
                    continue;
                }

                $studentsWithSchedule++;
                foreach ($normalized['items'] as $item) {
                    $rowsToSync[] = [
                        'nim' => $nim,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                        'kode_makul' => $item['kode_makul'],
                        'nama_makul' => $item['nama_makul'],
                        'nipy' => $item['nipy'],
                        'nama_dosen' => $item['nama_dosen'],
                    ];
                }
            }
        }

        $inserted = 0;
        $updated = 0;

        if (! empty($rowsToSync)) {
            DB::transaction(function () use ($rowsToSync, &$inserted, &$updated) {
                foreach ($rowsToSync as $row) {
                    $model = JatahMakul::updateOrCreate(
                        [
                            'nim' => $row['nim'],
                            'tahun_ajaran' => $row['tahun_ajaran'],
                            'semester' => $row['semester'],
                            'kode_makul' => $row['kode_makul'],
                        ],
                        [
                            'nama_makul' => $row['nama_makul'],
                            'nipy' => $row['nipy'],
                            'nama_dosen' => $row['nama_dosen'],
                        ]
                    );

                    if ($model->wasRecentlyCreated) {
                        $inserted++;
                    } else {
                        $updated++;
                    }
                }
            });
        }

        return [
            'processed_students' => $processedStudents,
            'students_with_schedule' => $studentsWithSchedule,
            'students_empty' => $studentsEmpty,
            'students_failed' => $studentsFailed,
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }

    /**
     * Menormalisasi struktur payload JSON dari API SIAMDOS menjadi daftar jadwal bersih.
     *
     * @param  array<mixed>  $payload
     * @param  array<string, string>|null  $dosenByNipy
     * @param  array<string, string>|null  $nipyByNamaLower
     * @return array{
     *     identitas: array<string, string>,
     *     parsed_tahun_ajaran: string,
     *     parsed_semester: string,
     *     items: list<array{kode_makul: string, nama_makul: string, nipy: string, nama_dosen: string}>
     * }
     */
    private function normalizeSiamdosPayload(
        array $payload,
        string $fallbackNim,
        ?array $dosenByNipy = null,
        ?array $nipyByNamaLower = null
    ): array {
        $rawIdentitas = is_array($payload['identitas'] ?? null) ? $payload['identitas'] : [];
        $rawTa = trim((string) ($rawIdentitas['tahun_ajaran'] ?? ''));

        $parsedTa = '';
        if (preg_match('/(\d{4})\s*\/\s*(\d{4})/', $rawTa, $m)) {
            $parsedTa = "{$m[1]}/{$m[2]}";
        }
        $parsedSem = '';
        if (stripos($rawTa, 'genap') !== false) {
            $parsedSem = 'genap';
        } elseif (stripos($rawTa, 'ganjil') !== false) {
            $parsedSem = 'ganjil';
        }

        $identitas = [
            'nim' => trim((string) ($rawIdentitas['nim'] ?? $fallbackNim)),
            'nama' => trim((string) ($rawIdentitas['nama'] ?? '')),
            'semester_ke' => trim((string) ($rawIdentitas['semester'] ?? '')),
            'tahun_ajaran' => $rawTa,
            'fakultas' => trim((string) ($rawIdentitas['fakultas'] ?? '')),
            'prodi' => trim((string) ($rawIdentitas['prodi'] ?? '')),
        ];

        $rawItems = [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            $rawItems = array_is_list($payload['data']) ? $payload['data'] : [];
        } elseif (array_is_list($payload)) {
            $rawItems = $payload;
        }

        if ($dosenByNipy === null && ! empty($rawItems)) {
            $dosenByNipy = Dosen::query()->pluck('nama', 'nipy')->all();
            $nipyByNamaLower = [];
            foreach ($dosenByNipy as $nipy => $nama) {
                $nipyByNamaLower[strtolower(trim((string) $nama))] = trim((string) $nipy);
            }
        }
        $dosenByNipy ??= [];
        $nipyByNamaLower ??= [];

        $dedup = [];
        foreach ($rawItems as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            // Gabungkan kemungkinan nested object (misal entry.mata_kuliah atau entry.dosen)
            $flat = $entry;
            foreach (['mata_kuliah', 'matakuliah', 'makul', 'mk', 'dosen', 'pengampu', 'jadwal'] as $nestedKey) {
                if (isset($entry[$nestedKey]) && is_array($entry[$nestedKey])) {
                    foreach ($entry[$nestedKey] as $k => $v) {
                        if (is_scalar($v) && ! isset($flat["{$nestedKey}_{$k}"])) {
                            $flat["{$nestedKey}_{$k}"] = $v;
                        }
                        if (is_scalar($v) && ! isset($flat[$k])) {
                            $flat[$k] = $v;
                        }
                    }
                }
            }

            $kodeMakul = $this->extractField($flat, [
                'kode_makul', 'kode_mk', 'kode_matakuliah', 'kode_mata_kuliah',
                'makul_kode', 'mata_kuliah_kode', 'mk_kode', 'kode',
            ]);
            $namaMakul = $this->extractField($flat, [
                'nama_makul', 'nama_mk', 'nama_matakuliah', 'nama_mata_kuliah',
                'mata_kuliah_nama', 'makul_nama', 'mk_nama', 'matakuliah', 'nama_kuliah', 'nama',
            ]);
            $nipy = $this->extractField($flat, [
                'nipy', 'nip_ynaa', 'nipy_dosen', 'nip_dosen', 'dosen_nipy',
                'dosen_nip_ynaa', 'dosen_nip', 'nip', 'nidn',
            ]);
            $namaDosen = $this->extractField($flat, [
                'nama_dosen', 'dosen_nama', 'dosen_pengampu', 'pengampu_nama',
                'nama_pengampu', 'dosen',
            ]);

            if ($kodeMakul === '' && $namaMakul === '') {
                continue;
            }
            if ($kodeMakul === '') {
                $kodeMakul = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $namaMakul) ?: 'MK', 0, 12));
            }
            if ($namaMakul === '') {
                $namaMakul = $kodeMakul;
            }

            if ($namaDosen === '' && $nipy !== '' && isset($dosenByNipy[$nipy])) {
                $namaDosen = (string) $dosenByNipy[$nipy];
            }
            if ($nipy === '' && $namaDosen !== '') {
                $lookup = $nipyByNamaLower[strtolower($namaDosen)] ?? '';
                if ($lookup !== '') {
                    $nipy = $lookup;
                }
            }

            $dedupKey = strtoupper($kodeMakul);
            $dedup[$dedupKey] = [
                'kode_makul' => $kodeMakul,
                'nama_makul' => $namaMakul,
                'nipy' => $nipy !== '' ? $nipy : '-',
                'nama_dosen' => $namaDosen !== '' ? $namaDosen : '-',
            ];
        }

        return [
            'identitas' => $identitas,
            'parsed_tahun_ajaran' => $parsedTa,
            'parsed_semester' => $parsedSem,
            'items' => array_values($dedup),
        ];
    }

    private function configureSiamdosRequest(PendingRequest $request): PendingRequest
    {
        $timeout = max(5, (int) config('services.siamdos.timeout', 30));
        $retryTimes = max(0, (int) config('services.siamdos.retry_times', 2));
        $verifySsl = (bool) config('services.siamdos.verify_ssl', true);

        $request = $request->acceptJson()
            ->timeout($timeout)
            ->connectTimeout(min(15, $timeout));

        if (! $verifySsl) {
            $request = $request->withoutVerifying();
        }

        if ($retryTimes > 0) {
            $request = $request->retry(
                $retryTimes,
                1000,
                function (Throwable $exception): bool {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }
                    if ($exception instanceof RequestException) {
                        $status = $exception->response?->status() ?? 0;

                        return $status >= 500 || $status === 429;
                    }

                    return false;
                },
                throw: false
            );
        }

        return $request;
    }

    private function executeSiamdosGet(string $endpoint): Response
    {
        $request = $this->configureSiamdosRequest(Http::acceptJson());

        try {
            return $request->get($endpoint);
        } catch (ConnectionException $e) {
            Log::error('Fetch jadwal SIAMDOS gagal: timeout atau server tidak merespon.', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Tidak dapat terhubung ke API SIAMDOS (timeout / server sedang tidak merespon). Silakan coba lagi.',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error('Fetch jadwal SIAMDOS gagal: error tak terduga.', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Gagal menghubungi API SIAMDOS: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Memetakan dan menyinkronkan array data mahasiswa ke tb_mahasiswa secara bertahap (chunk 500)
     * di dalam DB::transaction() menggunakan mekanisme anti-duplikat berbasis NIM.
     *
     * @param  array<int, mixed>  $records
     * @return array{status: string, total_api: int, processed: int, inserted: int, updated: int, unchanged: int, skipped: int}
     */
    public function syncRecords(array $records): array
    {
        $normalized = [];
        $skipped = 0;

        foreach ($records as $item) {
            if (! is_array($item)) {
                $skipped++;
                continue;
            }

            $nim = $this->extractField($item, ['nim', 'NIM', 'npm', 'NPM', 'no_induk', 'nim_mahasiswa']);
            $nama = $this->extractField($item, ['nama', 'NAMA', 'nama_mahasiswa', 'nama_mhs', 'nama_lengkap', 'name', 'full_name']);

            if ($nim === '' || $nama === '') {
                $skipped++;
                continue;
            }

            $normalized[$nim] = $nama;
        }

        $inserted = 0;
        $updated = 0;
        $unchanged = 0;

        try {
            DB::transaction(function () use ($normalized, &$inserted, &$updated, &$unchanged) {
                $existingRows = Mahasiswa::query()
                    ->select('id', 'nim', 'nama')
                    ->get();

                $existingByNim = [];
                foreach ($existingRows as $row) {
                    $key = trim((string) $row->nim);
                    if (! isset($existingByNim[$key])) {
                        $existingByNim[$key] = [
                            'id' => $row->id,
                            'nama' => trim((string) $row->nama),
                        ];
                    }
                }

                foreach (array_chunk($normalized, 500, true) as $batch) {
                    $toInsert = [];

                    foreach ($batch as $nim => $nama) {
                        if (! isset($existingByNim[$nim])) {
                            $toInsert[] = [
                                'nim' => $nim,
                                'nama' => $nama,
                            ];
                            $existingByNim[$nim] = ['id' => null, 'nama' => $nama];
                            $inserted++;
                        } elseif ($existingByNim[$nim]['nama'] !== $nama) {
                            Mahasiswa::where('id', $existingByNim[$nim]['id'])->update(['nama' => $nama]);
                            $existingByNim[$nim]['nama'] = $nama;
                            $updated++;
                        } else {
                            $unchanged++;
                        }
                    }

                    if (! empty($toInsert)) {
                        Mahasiswa::insert($toInsert);
                    }
                }
            });
        } catch (Throwable $e) {
            Log::error('Sinkronisasi SIAKAD gagal saat menyimpan ke tb_mahasiswa.', [
                'total_normalized' => count($normalized),
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Gagal menyimpan data mahasiswa ke database: ' . $e->getMessage(), previous: $e);
        }

        return [
            'status' => 'success',
            'total_api' => count($records),
            'processed' => $inserted + $updated + $unchanged,
            'inserted' => $inserted,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'skipped' => $skipped,
        ];
    }

    /**
     * Memetakan dan menyinkronkan array data dosen ke tb_dosen secara bertahap (chunk 500)
     * di dalam DB::transaction() menggunakan mekanisme anti-duplikat berbasis NIPY (`nip_ynaa` / `nipy`).
     *
     * @param  array<int, mixed>  $records
     * @return array{status: string, total_api: int, processed: int, inserted: int, updated: int, unchanged: int, skipped: int}
     */
    public function syncDosenRecords(array $records): array
    {
        $normalized = [];
        $skipped = 0;

        foreach ($records as $item) {
            if (! is_array($item)) {
                $skipped++;
                continue;
            }

            $nipy = $this->extractField($item, ['nip_ynaa', 'nipy', 'NIPY', 'nip', 'NIP']);
            $nama = $this->extractField($item, ['nama', 'NAMA', 'nama_dosen', 'nama_lengkap', 'name', 'full_name']);

            if ($nipy === '' || $nama === '') {
                $skipped++;
                continue;
            }

            $normalized[$nipy] = $nama;
        }

        $inserted = 0;
        $updated = 0;
        $unchanged = 0;

        try {
            DB::transaction(function () use ($normalized, &$inserted, &$updated, &$unchanged) {
                $existingRows = Dosen::query()
                    ->select('id', 'nipy', 'nama')
                    ->get();

                $existingByNipy = [];
                foreach ($existingRows as $row) {
                    $key = trim((string) $row->nipy);
                    if (! isset($existingByNipy[$key])) {
                        $existingByNipy[$key] = [
                            'id' => $row->id,
                            'nama' => trim((string) $row->nama),
                        ];
                    }
                }

                foreach (array_chunk($normalized, 500, true) as $batch) {
                    $toInsert = [];

                    foreach ($batch as $nipy => $nama) {
                        if (! isset($existingByNipy[$nipy])) {
                            $toInsert[] = [
                                'nipy' => $nipy,
                                'nama' => $nama,
                            ];
                            $existingByNipy[$nipy] = ['id' => null, 'nama' => $nama];
                            $inserted++;
                        } elseif ($existingByNipy[$nipy]['nama'] !== $nama) {
                            Dosen::where('id', $existingByNipy[$nipy]['id'])->update(['nama' => $nama]);
                            $existingByNipy[$nipy]['nama'] = $nama;
                            $updated++;
                        } else {
                            $unchanged++;
                        }
                    }

                    if (! empty($toInsert)) {
                        Dosen::insert($toInsert);
                    }
                }
            });
        } catch (Throwable $e) {
            Log::error('Sinkronisasi SIAKAD gagal saat menyimpan ke tb_dosen.', [
                'total_normalized' => count($normalized),
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Gagal menyimpan data dosen ke database: ' . $e->getMessage(), previous: $e);
        }

        return [
            'status' => 'success',
            'total_api' => count($records),
            'processed' => $inserted + $updated + $unchanged,
            'inserted' => $inserted,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'skipped' => $skipped,
        ];
    }

    /**
     * Helper penarikan seluruh halaman dari endpoint SIAKAD yang berpaginasi.
     *
     * @return array{total_pages: int, total_reported: int, records: array<int, mixed>}
     */
    private function fetchAllPaginated(string $path, string $entityLabel): array
    {
        $baseUrl = rtrim((string) config('services.siakad.url', 'https://apisiakad.ponpesnaa.net/v1'), '/');
        $endpoint = $baseUrl . '/' . ltrim($path, '/');

        $firstResponse = $this->executeGet($endpoint, ['page' => 1]);
        $firstPayload = $this->parseJsonResponse($firstResponse, $endpoint);
        $allRecords = $this->extractRecords($firstPayload, $entityLabel);

        $totalPages = $this->extractTotalPages($firstPayload);
        $totalReported = (int) ($firstPayload['total_data'] ?? $firstPayload['total'] ?? count($allRecords));

        if ($totalPages > 1) {
            $remainingPages = range(2, $totalPages);

            foreach (array_chunk($remainingPages, 5) as $pageBatch) {
                $responses = Http::pool(function (Pool $pool) use ($pageBatch, $endpoint) {
                    $requests = [];
                    foreach ($pageBatch as $page) {
                        $requests[$page] = $this->configureRequest($pool->as((string) $page))
                            ->get($endpoint, ['page' => $page]);
                    }

                    return $requests;
                });

                foreach ($pageBatch as $page) {
                    $res = $responses[(string) $page] ?? null;

                    if ($res instanceof Throwable) {
                        $res = $this->executeGet($endpoint, ['page' => $page]);
                    }

                    if (! $res instanceof Response) {
                        Log::error("Sinkronisasi SIAKAD ({$entityLabel}) gagal: respon halaman tidak valid.", [
                            'endpoint' => $endpoint,
                            'page' => $page,
                        ]);
                        throw new RuntimeException("Gagal mengambil data {$entityLabel} halaman {$page} dari API SIAKAD.");
                    }

                    $pagePayload = $this->parseJsonResponse($res, "{$endpoint}?page={$page}");
                    $pageRecords = $this->extractRecords($pagePayload, $entityLabel);
                    foreach ($pageRecords as $rec) {
                        $allRecords[] = $rec;
                    }
                }
            }
        }

        return [
            'total_pages' => $totalPages,
            'total_reported' => $totalReported,
            'records' => $allRecords,
        ];
    }

    /**
     * Mengonfigurasi timeout, SSL, retry, dan autentikasi pada instance HTTP request.
     */
    private function configureRequest(PendingRequest $request): PendingRequest
    {
        $authType = strtolower((string) config('services.siakad.auth_type', 'none'));
        $token = trim((string) config('services.siakad.token', ''));
        $apiKeyHeader = (string) config('services.siakad.api_key_header', 'X-API-Key');
        $timeout = max(5, (int) config('services.siakad.timeout', 60));
        $retryTimes = max(0, (int) config('services.siakad.retry_times', 2));
        $verifySsl = (bool) config('services.siakad.verify_ssl', true);

        $request = $request->acceptJson()
            ->timeout($timeout)
            ->connectTimeout(min(20, $timeout));

        if (! $verifySsl) {
            $request = $request->withoutVerifying();
        }

        if ($retryTimes > 0) {
            $request = $request->retry(
                $retryTimes,
                1000,
                function (Throwable $exception): bool {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }
                    if ($exception instanceof RequestException) {
                        $status = $exception->response?->status() ?? 0;

                        return $status >= 500 || $status === 429;
                    }

                    return false;
                },
                throw: false
            );
        }

        if ($token !== '') {
            if ($authType === 'api_key') {
                $request = $request->withHeaders([$apiKeyHeader => $token]);
            } else {
                $request = $request->withToken($token);
            }
        }

        return $request;
    }

    /**
     * Menjalankan GET request tunggal dengan penanganan exception & pencatatan Log::error.
     *
     * @param  array<string, mixed>  $query
     */
    private function executeGet(string $endpoint, array $query = []): Response
    {
        $request = $this->configureRequest(Http::acceptJson());

        try {
            return $request->get($endpoint, $query);
        } catch (ConnectionException $e) {
            Log::error('Sinkronisasi SIAKAD gagal: koneksi ke API terputus atau timeout.', [
                'endpoint' => $endpoint,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Tidak dapat terhubung ke server API SIAKAD (timeout / server tidak merespon). Silakan cek jaringan atau coba lagi.',
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error('Sinkronisasi SIAKAD gagal: error tak terduga saat memanggil API.', [
                'endpoint' => $endpoint,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Terjadi kesalahan saat menghubungi API SIAKAD: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Memvalidasi status HTTP dan mengembalikan body JSON sebagai array.
     *
     * @return array<mixed>
     */
    private function parseJsonResponse(Response $response, string $endpoint): array
    {
        if ($response->failed()) {
            $status = $response->status();
            $bodyPreview = mb_substr((string) $response->body(), 0, 500);

            Log::error('Sinkronisasi SIAKAD gagal: API mengembalikan status error HTTP.', [
                'endpoint' => $endpoint,
                'status' => $status,
                'body_preview' => $bodyPreview,
            ]);

            $pesan = match (true) {
                $status === 401 || $status === 403 => "Akses ditolak oleh API SIAKAD (HTTP {$status}). Periksa SIAKAD_API_TOKEN dan SIAKAD_AUTH_TYPE di file .env.",
                $status === 404 => "Endpoint API SIAKAD tidak ditemukan (HTTP 404): {$endpoint}.",
                $status >= 500 => "Server API SIAKAD mengalami gangguan internal (HTTP {$status}).",
                default => "API SIAKAD mengembalikan status gagal (HTTP {$status}).",
            };

            throw new RuntimeException($pesan);
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            Log::error('Sinkronisasi SIAKAD gagal: respon bukan JSON valid.', [
                'endpoint' => $endpoint,
                'body_preview' => mb_substr((string) $response->body(), 0, 300),
            ]);

            throw new RuntimeException('Format respon API SIAKAD bukan JSON yang valid.');
        }

        return $payload;
    }

    /**
     * Membaca jumlah total halaman dari metadata pagination API (jika ada).
     *
     * @param  array<mixed>  $payload
     */
    private function extractTotalPages(array $payload): int
    {
        foreach (['total_page', 'total_pages', 'last_page'] as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return max(1, (int) $payload[$key]);
            }
        }

        if (isset($payload['meta']) && is_array($payload['meta'])) {
            foreach (['total_page', 'total_pages', 'last_page'] as $key) {
                if (isset($payload['meta'][$key]) && is_numeric($payload['meta'][$key])) {
                    return max(1, (int) $payload['meta'][$key]);
                }
            }
        }

        return 1;
    }

    /**
     * Mengekstrak daftar array data baik array langsung maupun dibungkus key `data` dsb.
     *
     * @param  array<mixed>  $payload
     * @return array<int, mixed>
     */
    private function extractRecords(array $payload, string $entityLabel): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['data', 'mahasiswa', 'dosen', 'result', 'results', 'items', 'records'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                if (array_is_list($payload[$key])) {
                    return $payload[$key];
                }
                if (isset($payload[$key]['data']) && is_array($payload[$key]['data']) && array_is_list($payload[$key]['data'])) {
                    return $payload[$key]['data'];
                }
            }
        }

        Log::error("Sinkronisasi SIAKAD ({$entityLabel}) gagal: struktur daftar data tidak dikenali.", [
            'top_keys' => array_keys($payload),
        ]);

        throw new RuntimeException("Struktur array data {$entityLabel} tidak ditemukan pada respon JSON API SIAKAD.");
    }

    /**
     * Mengambil nilai field string pertama yang tidak kosong dari daftar kandidat nama kolom.
     *
     * @param  array<string, mixed>  $item
     * @param  list<string>  $candidates
     */
    private function extractField(array $item, array $candidates): string
    {
        foreach ($candidates as $key) {
            if (array_key_exists($key, $item) && is_scalar($item[$key])) {
                $val = trim((string) $item[$key]);
                if ($val !== '') {
                    return $val;
                }
            }
        }

        return '';
    }
}
