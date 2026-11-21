<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Hasil;
use App\Models\JatahMakul;
use App\Models\Ketuntasan;
use App\Models\Mahasiswa;
use App\Models\Pertanyaan;
use App\Models\Quisioner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $nim = (string) ($request->session()->get('mahasiswa_nim') ?? $request->user()->username);
        $namaMahasiswa = (string) ($request->session()->get('mahasiswa_nama') ?? '');

        if ($namaMahasiswa === '') {
            $mhs = Mahasiswa::where('nim', $nim)->first(['nim', 'nama']);
            $namaMahasiswa = $mhs?->nama ?: $nim;
        }

        // Query 1: Seluruh quisioner aktif (hapus = '0') beserta jumlah pertanyaan
        $quisionerAktif = Quisioner::aktif()
            ->withCount('pertanyaan')
            ->orderByDesc('id')
            ->get();

        // Query 2: Seluruh catatan ketuntasan milik mahasiswa ini (dipakai untuk map & riwayat)
        $riwayatKetuntasan = Ketuntasan::where('niim', $nim)
            ->orderByDesc('id')
            ->get();

        $ketuntasanMap = $riwayatKetuntasan->keyBy('id_quisioner');

        // Peta judul quisioner untuk riwayat tanpa N+1 query
        $quisionerTitles = $quisionerAktif->pluck('judul_quisioner', 'id')->all();
        $missingQIds = $riwayatKetuntasan
            ->pluck('id_quisioner')
            ->unique()
            ->diff(array_keys($quisionerTitles))
            ->values()
            ->all();

        if (! empty($missingQIds)) {
            $extraTitles = Quisioner::whereIn('id', $missingQIds)
                ->pluck('judul_quisioner', 'id')
                ->all();
            $quisionerTitles += $extraTitles;
        }

        // Query 3: Seluruh jatah mata kuliah mahasiswa ini
        $allMakul = JatahMakul::where('nim', $nim)
            ->orderBy('nama_makul')
            ->get();

        // Query 4 & 5: Khusus EDOM aktif, bangun O(1) lookup map dari pasangan (id_quisioner|kode_makul|nipy) yang sudah diisi
        $edomIds = $quisionerAktif->where('type', 'edom')->pluck('id')->values()->all();
        $edomFilledLookup = [];

        if (! empty($edomIds)) {
            $pertanyaanRows = Pertanyaan::whereIn('id_quisioner', $edomIds)
                ->get(['id', 'id_quisioner']);

            $pertanyaanToQuisioner = $pertanyaanRows->pluck('id_quisioner', 'id')->all();
            $edomPertanyaanIds = array_keys($pertanyaanToQuisioner);

            if (! empty($edomPertanyaanIds)) {
                $hasilDistinct = Hasil::where('nim', $nim)
                    ->whereIn('id_pertanyaan', $edomPertanyaanIds)
                    ->select('id_pertanyaan', 'kode_makul', 'nipy')
                    ->distinct()
                    ->get();

                foreach ($hasilDistinct as $h) {
                    $qId = $pertanyaanToQuisioner[$h->id_pertanyaan] ?? null;
                    if ($qId !== null) {
                        $key = $qId . '|' . trim((string) $h->kode_makul) . '|' . trim((string) $h->nipy);
                        $edomFilledLookup[$key] = true;
                    }
                }
            }
        }

        // Tentukan Tahun Ajaran & Semester aktif untuk Banner Sambutan
        $latestQuisioner = $quisionerAktif->first();
        $latestMakul = $allMakul->sortByDesc('tahun_ajaran')->first();
        $tahunAjaranAktif = (string) ($latestQuisioner?->tahun_ajaran ?: ($latestMakul?->tahun_ajaran ?: date('Y') . '/' . (date('Y') + 1)));
        $semesterAktif = (string) ($latestQuisioner?->semester ?: ($latestMakul?->semester ?: 'ganjil'));

        // Bangun daftar item status ketuntasan terpadu (tb_quisioner + tb_ketuntasan + tb_jatah_makul)
        $checklistItems = collect();

        foreach ($quisionerAktif as $q) {
            $record = $ketuntasanMap->get($q->id);

            if ($q->type === 'edom') {
                $makulPeriode = $allMakul->filter(
                    fn (JatahMakul $m) => trim((string) $m->tahun_ajaran) === trim((string) $q->tahun_ajaran)
                        && strtolower((string) $m->semester) === strtolower((string) $q->semester)
                )->values();

                if ($makulPeriode->isEmpty()) {
                    $makulPeriode = $allMakul->filter(
                        fn (JatahMakul $m) => strtolower((string) $m->semester) === strtolower((string) $q->semester)
                    )->values();
                }

                if ($makulPeriode->isNotEmpty()) {
                    foreach ($makulPeriode as $m) {
                        $lookupKey = $q->id . '|' . trim((string) $m->kode_makul) . '|' . trim((string) $m->nipy);
                        $isTuntas = isset($edomFilledLookup[$lookupKey])
                            || ($record && $record->ketuntasan === '1');

                        $checklistItems->push([
                            'quisioner' => $q,
                            'type' => 'edom',
                            'judul' => $q->judul_quisioner,
                            'tahun_ajaran' => (string) $q->tahun_ajaran,
                            'semester' => (string) $q->semester,
                            'pertanyaan_count' => (int) $q->pertanyaan_count,
                            'kode_makul' => (string) $m->kode_makul,
                            'nama_makul' => (string) $m->nama_makul,
                            'nama_dosen' => (string) $m->nama_dosen,
                            'nipy' => (string) $m->nipy,
                            'makul_missing' => false,
                            'is_tuntas' => $isTuntas,
                            'fill_url' => $q->pertanyaan_count > 0
                                ? route('mahasiswa.quisioner.show', [$q, 'makul' => $m->id])
                                : null,
                            'search_text' => strtolower(trim(
                                "{$q->judul_quisioner} {$m->kode_makul} {$m->nama_makul} {$m->nama_dosen} {$m->nipy} {$q->tahun_ajaran} {$q->semester} edom"
                            )),
                        ]);
                    }
                } else {
                    $isTuntas = (bool) ($record && $record->ketuntasan === '1');
                    $checklistItems->push([
                        'quisioner' => $q,
                        'type' => 'edom',
                        'judul' => $q->judul_quisioner,
                        'tahun_ajaran' => (string) $q->tahun_ajaran,
                        'semester' => (string) $q->semester,
                        'pertanyaan_count' => (int) $q->pertanyaan_count,
                        'kode_makul' => null,
                        'nama_makul' => null,
                        'nama_dosen' => null,
                        'nipy' => null,
                        'makul_missing' => true,
                        'is_tuntas' => $isTuntas,
                        'fill_url' => route('mahasiswa.quisioner.index', ['type' => 'edom']),
                        'search_text' => strtolower(trim(
                            "{$q->judul_quisioner} {$q->tahun_ajaran} {$q->semester} edom"
                        )),
                    ]);
                }
            } else {
                $isTuntas = (bool) ($record && $record->ketuntasan === '1');
                $checklistItems->push([
                    'quisioner' => $q,
                    'type' => 'biasa',
                    'judul' => $q->judul_quisioner,
                    'tahun_ajaran' => (string) $q->tahun_ajaran,
                    'semester' => (string) $q->semester,
                    'pertanyaan_count' => (int) $q->pertanyaan_count,
                    'kode_makul' => null,
                    'nama_makul' => null,
                    'nama_dosen' => null,
                    'nipy' => null,
                    'makul_missing' => false,
                    'is_tuntas' => $isTuntas,
                    'fill_url' => $q->pertanyaan_count > 0
                        ? route('mahasiswa.quisioner.show', $q)
                        : null,
                    'search_text' => strtolower(trim(
                        "{$q->judul_quisioner} {$q->tahun_ajaran} {$q->semester} biasa umum"
                    )),
                ]);
            }
        }

        // Prioritaskan item yang Belum Diisi di bagian atas agar mahasiswa langsung melihat tanggungan
        $checklistItems = $checklistItems
            ->sortBy(fn (array $item) => $item['is_tuntas'] ? 1 : 0)
            ->values();

        $totalWajib = $checklistItems->count();
        $sudahTuntas = $checklistItems->where('is_tuntas', true)->count();
        $belumTuntas = max(0, $totalWajib - $sudahTuntas);
        $persentaseKetuntasan = $totalWajib > 0 ? (int) round(($sudahTuntas / $totalWajib) * 100) : 0;
        $isAllTuntas = $totalWajib > 0 && $belumTuntas === 0;

        return view('mahasiswa.dashboard', [
            'namaMahasiswa' => $namaMahasiswa,
            'nimMahasiswa' => $nim,
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'semesterAktif' => $semesterAktif,
            'jumlahMakul' => $allMakul->count(),
            'totalWajib' => $totalWajib,
            'sudahTuntas' => $sudahTuntas,
            'belumTuntas' => $belumTuntas,
            'persentaseKetuntasan' => $persentaseKetuntasan,
            'isAllTuntas' => $isAllTuntas,
            'checklistItems' => $checklistItems,
            'riwayatKetuntasan' => $riwayatKetuntasan,
            'quisionerTitles' => $quisionerTitles,
        ]);
    }
}
