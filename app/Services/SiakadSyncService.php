<?php

namespace App\Services;

use App\Models\Dosen;
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
