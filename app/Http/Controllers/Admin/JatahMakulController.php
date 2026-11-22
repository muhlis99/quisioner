<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\JatahMakul;
use App\Models\Mahasiswa;
use App\Services\SiakadSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class JatahMakulController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $semester = (string) $request->query('semester', '');
        $tahunAjaran = trim((string) $request->query('tahun_ajaran', ''));
        $status = (string) $request->query('status', '');

        if (! in_array($status, ['sudah', 'belum'], true)) {
            $status = '';
        }

        $tahunAjaranList = JatahMakul::query()
            ->select('tahun_ajaran')
            ->whereNotNull('tahun_ajaran')
            ->where('tahun_ajaran', '!=', '')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran');

        $periodFilter = function ($query) use ($semester, $tahunAjaran): void {
            $query
                ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
                ->when($tahunAjaran !== '', fn ($q) => $q->where('tahun_ajaran', $tahunAjaran));
        };

        // Ringkasan kompak per periode aktif (tanpa N+1)
        $totalMahasiswa = Mahasiswa::count();
        $sudahAdaMakul = Mahasiswa::query()
            ->whereHas('jatahMakul', $periodFilter)
            ->count();
        $belumAdaMakul = max(0, $totalMahasiswa - $sudahAdaMakul);
        $totalPemetaan = JatahMakul::query()
            ->tap($periodFilter)
            ->count();

        // Daftar unik per-mahasiswa + eager loading rincian mata kuliah sesuai filter periode
        $mahasiswas = Mahasiswa::query()
            ->select(['id', 'nim', 'nama'])
            ->withCount(['jatahMakul as total_makul' => $periodFilter])
            ->with([
                'jatahMakul' => function ($q) use ($periodFilter) {
                    $periodFilter($q);
                    $q->orderByDesc('tahun_ajaran')
                        ->orderBy('semester')
                        ->orderBy('nama_makul');
                },
            ])
            ->when($search !== '', function ($query) use ($search, $periodFilter) {
                $query->where(function ($sub) use ($search, $periodFilter) {
                    $sub->where('nim', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhereHas('jatahMakul', function ($jm) use ($search, $periodFilter) {
                            $periodFilter($jm);
                            $jm->where(function ($detail) use ($search) {
                                $detail->where('kode_makul', 'like', "%{$search}%")
                                    ->orWhere('nama_makul', 'like', "%{$search}%")
                                    ->orWhere('nipy', 'like', "%{$search}%")
                                    ->orWhere('nama_dosen', 'like', "%{$search}%");
                            });
                        });
                });
            })
            ->when($status === 'sudah', fn ($q) => $q->whereHas('jatahMakul', $periodFilter))
            ->when($status === 'belum', fn ($q) => $q->whereDoesntHave('jatahMakul', $periodFilter))
            ->orderByDesc('total_makul')
            ->orderBy('nim')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jatah-makul.index', [
            'mahasiswas' => $mahasiswas,
            'search' => $search,
            'semester' => $semester,
            'tahunAjaran' => $tahunAjaran,
            'status' => $status,
            'tahunAjaranList' => $tahunAjaranList,
            'summary' => [
                'total_mahasiswa' => $totalMahasiswa,
                'sudah_ada_makul' => $sudahAdaMakul,
                'belum_ada_makul' => $belumAdaMakul,
                'total_pemetaan' => $totalPemetaan,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $mahasiswas = Mahasiswa::orderBy('nim')->get(['id', 'nim', 'nama']);
        $dosens = Dosen::orderBy('nama')->get(['id', 'nipy', 'nama']);
        $preselectedNim = trim((string) $request->query('nim', ''));

        // Daftar unik mata kuliah dari tb_jatah_makul untuk Searchable Combobox Mode Manual
        $makulCatalog = DB::table('tb_jatah_makul')
            ->select('kode_makul', DB::raw('MAX(nama_makul) as nama_makul'))
            ->whereNotNull('kode_makul')
            ->where('kode_makul', '!=', '')
            ->groupBy('kode_makul')
            ->orderBy('nama_makul')
            ->get()
            ->map(fn ($row) => [
                'kode_makul' => trim((string) $row->kode_makul),
                'nama_makul' => trim((string) $row->nama_makul),
            ])
            ->values();

        return view('admin.jatah-makul.create', [
            'mahasiswas' => $mahasiswas,
            'dosens' => $dosens,
            'makulCatalog' => $makulCatalog,
            'defaultTahunAjaran' => '2026/2027',
            'preselectedNim' => $preselectedNim,
        ]);
    }

    /**
     * Mode 1 (API): Tarik & preview jadwal kuliah 1 mahasiswa dari API SIAMDOS.
     */
    public function fetchFromApi(Request $request, SiakadSyncService $syncService): JsonResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:100'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
        ], [
            'nim.required' => 'Silakan pilih mahasiswa terlebih dahulu.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
        ]);

        $nim = trim($validated['nim']);
        $tahunAjaran = trim($validated['tahun_ajaran']);
        $semester = $validated['semester'];

        try {
            $fetched = $syncService->fetchJadwalMahasiswa($nim);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 502);
        }

        $existingKodes = JatahMakul::query()
            ->where('nim', $nim)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->pluck('kode_makul')
            ->map(fn ($k) => strtoupper(trim((string) $k)))
            ->flip()
            ->all();

        $items = [];
        $existingCount = 0;
        $newCount = 0;

        foreach ($fetched['items'] as $item) {
            $exists = isset($existingKodes[strtoupper(trim($item['kode_makul']))]);
            if ($exists) {
                $existingCount++;
            } else {
                $newCount++;
            }

            $items[] = [
                'kode_makul' => $item['kode_makul'],
                'nama_makul' => $item['nama_makul'],
                'nipy' => $item['nipy'],
                'nama_dosen' => $item['nama_dosen'],
                'already_exists' => $exists,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => count($items) > 0
                ? 'Berhasil menarik ' . count($items) . ' jadwal mata kuliah dari API SIAMDOS.'
                : 'API SIAMDOS merespons sukses, namun belum ada jadwal mata kuliah untuk NIM ini pada periode aktif.',
            'identitas' => $fetched['identitas'],
            'parsed_tahun_ajaran' => $fetched['parsed_tahun_ajaran'],
            'parsed_semester' => $fetched['parsed_semester'],
            'summary' => [
                'total' => count($items),
                'new' => $newCount,
                'existing' => $existingCount,
            ],
            'items' => $items,
        ]);
    }

    /**
     * Mode 1 (API): Simpan jadwal terpilih hasil preview ke tb_jatah_makul secara anti-duplikat.
     */
    public function storeFromApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:100'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kode_makul' => ['required', 'string', 'max:100'],
            'items.*.nama_makul' => ['required', 'string', 'max:255'],
            'items.*.nipy' => ['nullable', 'string', 'max:100'],
            'items.*.nama_dosen' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Pilih minimal satu mata kuliah untuk disimpan.',
            'items.min' => 'Pilih minimal satu mata kuliah untuk disimpan.',
        ]);

        $nim = trim($validated['nim']);
        $tahunAjaran = trim($validated['tahun_ajaran']);
        $semester = $validated['semester'];

        $dosenMap = Dosen::query()->pluck('nama', 'nipy')->all();
        $inserted = 0;
        $updated = 0;

        DB::transaction(function () use ($validated, $nim, $tahunAjaran, $semester, $dosenMap, &$inserted, &$updated) {
            foreach ($validated['items'] as $row) {
                $kode = trim((string) $row['kode_makul']);
                $namaMakul = trim((string) $row['nama_makul']);
                $nipy = trim((string) ($row['nipy'] ?? ''));
                $namaDosen = trim((string) ($row['nama_dosen'] ?? ''));

                if ($namaDosen === '' && $nipy !== '' && isset($dosenMap[$nipy])) {
                    $namaDosen = (string) $dosenMap[$nipy];
                }

                $model = JatahMakul::updateOrCreate(
                    [
                        'nim' => $nim,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                        'kode_makul' => $kode,
                    ],
                    [
                        'nama_makul' => $namaMakul,
                        'nipy' => $nipy !== '' ? $nipy : '-',
                        'nama_dosen' => $namaDosen !== '' ? $namaDosen : '-',
                    ]
                );

                if ($model->wasRecentlyCreated) {
                    $inserted++;
                } else {
                    $updated++;
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => "Jadwal kuliah berhasil disimpan ({$inserted} baru, {$updated} diperbarui).",
            'data' => [
                'inserted' => $inserted,
                'updated' => $updated,
                'total' => $inserted + $updated,
            ],
        ]);
    }

    /**
     * Mode 1 (Bonus): Generate massal jadwal kuliah per-batch mahasiswa agar bebas timeout & mendukung progress bar.
     */
    public function generateMassal(Request $request, SiakadSyncService $syncService): JsonResponse
    {
        $validated = $request->validate([
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);

        $tahunAjaran = trim($validated['tahun_ajaran']);
        $semester = $validated['semester'];
        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? 10);

        $totalStudents = Mahasiswa::count();
        $nims = Mahasiswa::query()
            ->orderBy('id')
            ->skip($offset)
            ->take($limit)
            ->pluck('nim')
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->values()
            ->all();

        if (empty($nims)) {
            return response()->json([
                'status' => 'success',
                'done' => true,
                'total_students' => $totalStudents,
                'next_offset' => $offset,
                'batch' => [
                    'processed_students' => 0,
                    'students_with_schedule' => 0,
                    'students_empty' => 0,
                    'students_failed' => 0,
                    'inserted' => 0,
                    'updated' => 0,
                ],
            ]);
        }

        $batchResult = $syncService->syncJadwalMahasiswaBatch($nims, $tahunAjaran, $semester);
        $nextOffset = $offset + count($nims);

        return response()->json([
            'status' => 'success',
            'done' => $nextOffset >= $totalStudents,
            'total_students' => $totalStudents,
            'next_offset' => $nextOffset,
            'batch' => $batchResult,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:100'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'kode_makul' => ['required', 'string', 'max:100'],
            'nama_makul' => ['required', 'string', 'max:255'],
            'nipy' => ['required', 'string', 'max:100'],
            'nama_dosen' => ['nullable', 'string', 'max:255'],
        ], [
            'nim.required' => 'NIM mahasiswa wajib dipilih.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'kode_makul.required' => 'Kode mata kuliah wajib diisi.',
            'nama_makul.required' => 'Nama mata kuliah wajib diisi.',
            'nipy.required' => 'Dosen pengampu wajib dipilih.',
        ]);

        $nim = trim($validated['nim']);
        $tahunAjaran = trim($validated['tahun_ajaran']);
        $semester = $validated['semester'];
        $kodeMakul = trim($validated['kode_makul']);
        $namaMakul = trim($validated['nama_makul']);
        $nipy = trim($validated['nipy']);
        $namaDosen = trim((string) ($validated['nama_dosen'] ?? ''));

        if ($namaDosen === '') {
            $dosen = Dosen::where('nipy', $nipy)->first();
            $namaDosen = $dosen ? $dosen->nama : '-';
        }

        $model = JatahMakul::updateOrCreate(
            [
                'nim' => $nim,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'kode_makul' => $kodeMakul,
            ],
            [
                'nama_makul' => $namaMakul,
                'nipy' => $nipy,
                'nama_dosen' => $namaDosen,
            ]
        );

        $pesan = $model->wasRecentlyCreated
            ? 'Data jatah mata kuliah berhasil ditambahkan.'
            : 'Data jatah mata kuliah yang sudah ada berhasil diperbarui.';

        return redirect()
            ->route('admin.jatah-makul.index')
            ->with('success', $pesan);
    }

    public function edit(JatahMakul $jatahMakul): View
    {
        return view('admin.jatah-makul.edit', [
            'jatahMakul' => $jatahMakul,
            'mahasiswas' => Mahasiswa::orderBy('nim')->get(),
            'dosens' => Dosen::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, JatahMakul $jatahMakul): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:100'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'kode_makul' => ['required', 'string', 'max:100'],
            'nama_makul' => ['required', 'string', 'max:255'],
            'nipy' => ['required', 'string', 'max:100'],
            'nama_dosen' => ['nullable', 'string', 'max:255'],
        ], [
            'nim.required' => 'NIM mahasiswa wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'kode_makul.required' => 'Kode mata kuliah wajib diisi.',
            'nama_makul.required' => 'Nama mata kuliah wajib diisi.',
            'nipy.required' => 'NIPY dosen wajib diisi.',
        ]);

        if (empty(trim((string) ($validated['nama_dosen'] ?? '')))) {
            $dosen = Dosen::where('nipy', trim($validated['nipy']))->first();
            $validated['nama_dosen'] = $dosen ? $dosen->nama : '-';
        }

        $jatahMakul->update($validated);

        return redirect()
            ->route('admin.jatah-makul.index')
            ->with('success', 'Data jatah mata kuliah berhasil diperbarui.');
    }

    public function destroy(JatahMakul $jatahMakul): RedirectResponse
    {
        $jatahMakul->delete();

        return redirect()
            ->route('admin.jatah-makul.index')
            ->with('success', 'Data jatah mata kuliah berhasil dihapus.');
    }
}
