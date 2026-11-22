<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Hasil;
use App\Models\Mahasiswa;
use App\Models\Quisioner;
use App\Models\Skor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $type = (string) $request->query('type', '');

        if (! in_array($type, ['edom', 'biasa'], true)) {
            $type = '';
        }

        $searchFilter = function ($query) use ($search): void {
            $query->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul_quisioner', 'like', "%{$search}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$search}%");
                });
            });
        };

        // Hitung jumlah per tipe (menghormati pencarian aktif) untuk Segmented Pill Filter & KPI
        $countEdom = Quisioner::aktif()->tap($searchFilter)->where('type', 'edom')->count();
        $countBiasa = Quisioner::aktif()->tap($searchFilter)->where('type', 'biasa')->count();
        $countAll = $countEdom + $countBiasa;

        // Ambil seluruh ID quisioner sesuai filter aktif untuk agregasi statistik responden & jawaban (1 query)
        $filteredIds = Quisioner::aktif()
            ->tap($searchFilter)
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->pluck('id')
            ->all();

        $quisioners = Quisioner::aktif()
            ->withCount('pertanyaan')
            ->tap($searchFilter)
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Hitung jumlah responden unik & total entri jawaban per quisioner
        $statsByQuisioner = [];
        $totalRespondenSum = 0;
        $totalJawabanSum = 0;

        if (! empty($filteredIds)) {
            $rows = DB::table('tb_hasil')
                ->join('tb_pertanyaan', 'tb_pertanyaan.id', '=', 'tb_hasil.id_pertanyaan')
                ->whereIn('tb_pertanyaan.id_quisioner', $filteredIds)
                ->select(
                    'tb_pertanyaan.id_quisioner',
                    DB::raw('COUNT(DISTINCT tb_hasil.nim) as total_responden'),
                    DB::raw('COUNT(tb_hasil.id) as total_jawaban')
                )
                ->groupBy('tb_pertanyaan.id_quisioner')
                ->get();

            foreach ($rows as $row) {
                $responden = (int) $row->total_responden;
                $jawaban = (int) $row->total_jawaban;
                $statsByQuisioner[(int) $row->id_quisioner] = [
                    'responden' => $responden,
                    'jawaban' => $jawaban,
                ];
                $totalRespondenSum += $responden;
                $totalJawabanSum += $jawaban;
            }
        }

        // Periode aktif / terbaru & total mahasiswa aktif untuk indikator partisipasi
        $latestQuisioner = Quisioner::aktif()
            ->orderByDesc('tahun_ajaran')
            ->orderByDesc('id')
            ->first(['tahun_ajaran', 'semester']);

        $periodeTerbaru = $latestQuisioner
            ? trim((string) $latestQuisioner->tahun_ajaran) . ' • ' . ucfirst((string) $latestQuisioner->semester)
            : 'Belum Ada Periode';

        $totalMahasiswa = Mahasiswa::count();

        return view('admin.laporan.index', [
            'quisioners' => $quisioners,
            'statsByQuisioner' => $statsByQuisioner,
            'search' => $search,
            'type' => $type,
            'totalMahasiswa' => $totalMahasiswa,
            'typeCounts' => [
                'all' => $countAll,
                'edom' => $countEdom,
                'biasa' => $countBiasa,
            ],
            'kpi' => [
                'total_quisioner' => count($filteredIds),
                'total_edom' => $countEdom,
                'total_biasa' => $countBiasa,
                'total_responden' => $totalRespondenSum,
                'total_jawaban' => $totalJawabanSum,
                'periode_terbaru' => $periodeTerbaru,
            ],
        ]);
    }

    public function show(Request $request, Quisioner $quisioner): View
    {
        abort_if($quisioner->hapus === '1', 404);

        $filterNipy = trim((string) $request->query('nipy', ''));
        $pertanyaans = $quisioner->pertanyaan()
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        $pertanyaanIds = $pertanyaans->pluck('id')->all();
        $masterSkors = Skor::orderBy('skor')->get();

        // Jika tb_skor masih kosong, tetap ambil nilai skor unik yang muncul di tb_hasil
        $baseHasilQuery = Hasil::query()
            ->whereIn('id_pertanyaan', empty($pertanyaanIds) ? [-1] : $pertanyaanIds)
            ->when($filterNipy !== '', fn ($q) => $q->where('nipy', $filterNipy));

        $totalResponden = (clone $baseHasilQuery)->distinct('nim')->count('nim');
        $totalJawaban = (clone $baseHasilQuery)->count();

        // Daftar dosen yang memiliki jawaban pada kuisioner ini (khusus filter EDOM)
        $dosenFilterList = collect();
        if ($quisioner->type === 'edom' && ! empty($pertanyaanIds)) {
            $nipyList = Hasil::query()
                ->whereIn('id_pertanyaan', $pertanyaanIds)
                ->whereNotNull('nipy')
                ->where('nipy', '!=', '')
                ->distinct()
                ->pluck('nipy');

            if ($nipyList->isNotEmpty()) {
                $dosenMap = Dosen::whereIn('nipy', $nipyList)->pluck('nama', 'nipy');
                $dosenFilterList = $nipyList->map(fn ($nipy) => [
                    'nipy' => $nipy,
                    'nama' => $dosenMap[$nipy] ?? $nipy,
                ])->sortBy('nama')->values();
            }
        }

        // Hitung distribusi skor hanya untuk pertanyaan pilihan ganda
        $gandaIds = $pertanyaans->where('type', 'ganda')->pluck('id')->all();
        $aggRows = empty($gandaIds)
            ? collect()
            : (clone $baseHasilQuery)
                ->whereIn('id_pertanyaan', $gandaIds)
                ->whereNotNull('skor')
                ->select('id_pertanyaan', 'skor', DB::raw('COUNT(*) as jumlah'))
                ->groupBy('id_pertanyaan', 'skor')
                ->get();

        // Daftar kolom skor yang akan ditampilkan pada tabel & chart
        $skorColumns = $masterSkors->map(fn (Skor $s) => [
            'skor' => (int) $s->skor,
            'keterangan' => $s->keterangan,
            'huruf' => $s->huruf,
        ])->values();

        // Tambahkan skor dari tb_hasil jika ada yang belum terdaftar di tb_skor
        $knownSkors = $skorColumns->pluck('skor')->all();
        foreach ($aggRows->pluck('skor')->unique()->sort() as $foundSkor) {
            $intSkor = (int) $foundSkor;
            if (! in_array($intSkor, $knownSkors, true)) {
                $skorColumns->push([
                    'skor' => $intSkor,
                    'keterangan' => 'Skor ' . $intSkor,
                    'huruf' => (string) $intSkor,
                ]);
                $knownSkors[] = $intSkor;
            }
        }
        $skorColumns = $skorColumns->sortBy('skor')->values();

        // Index cepat: [id_pertanyaan][skor] => jumlah
        $countsByQuestion = [];
        $totalBySkor = [];
        foreach ($skorColumns as $col) {
            $totalBySkor[$col['skor']] = 0;
        }

        $sumWeightedAll = 0;
        $sumCountAll = 0;

        foreach ($aggRows as $row) {
            $qId = (int) $row->id_pertanyaan;
            $sVal = (int) $row->skor;
            $cnt = (int) $row->jumlah;

            $countsByQuestion[$qId][$sVal] = $cnt;
            $totalBySkor[$sVal] = ($totalBySkor[$sVal] ?? 0) + $cnt;
            $sumWeightedAll += ($sVal * $cnt);
            $sumCountAll += $cnt;
        }

        $rataRataKeseluruhan = $sumCountAll > 0 ? round($sumWeightedAll / $sumCountAll, 2) : 0.0;

        // Rekap per pertanyaan pilihan ganda
        $rekapGanda = [];
        $nomor = 1;
        foreach ($pertanyaans->where('type', 'ganda') as $p) {
            $dist = [];
            $totalQ = 0;
            $weightedQ = 0;

            foreach ($skorColumns as $col) {
                $sVal = $col['skor'];
                $jumlah = $countsByQuestion[$p->id][$sVal] ?? 0;
                $dist[$sVal] = $jumlah;
                $totalQ += $jumlah;
                $weightedQ += ($sVal * $jumlah);
            }

            $rekapGanda[] = [
                'nomor' => $nomor++,
                'id' => $p->id,
                'kategori' => $p->kategori,
                'isi_pertanyaan' => $p->isi_pertanyaan,
                'distribusi' => $dist,
                'total' => $totalQ,
                'rata_rata' => $totalQ > 0 ? round($weightedQ / $totalQ, 2) : 0.0,
            ];
        }

        // Jawaban esai (jika ada pertanyaan bertipe 'esai')
        $esaiIds = $pertanyaans->where('type', 'esai')->pluck('id')->all();
        $jawabanEsai = collect();
        if (! empty($esaiIds)) {
            $jawabanEsai = (clone $baseHasilQuery)
                ->whereIn('id_pertanyaan', $esaiIds)
                ->whereNotNull('isi_jawaban')
                ->where('isi_jawaban', '!=', '')
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->limit(100)
                ->get();
        }

        // Data untuk Chart.js
        $chartDistribusi = [
            'labels' => $skorColumns->map(fn ($c) => "Skor {$c['skor']} ({$c['keterangan']})")->values()->all(),
            'data' => $skorColumns->map(fn ($c) => $totalBySkor[$c['skor']] ?? 0)->values()->all(),
        ];

        $chartPerPertanyaan = [
            'labels' => array_map(fn ($r) => 'P' . $r['nomor'], $rekapGanda),
            'fullLabels' => array_map(fn ($r) => $r['isi_pertanyaan'], $rekapGanda),
            'data' => array_map(fn ($r) => $r['rata_rata'], $rekapGanda),
        ];

        return view('admin.laporan.show', [
            'quisioner' => $quisioner,
            'skorColumns' => $skorColumns,
            'totalBySkor' => $totalBySkor,
            'rekapGanda' => $rekapGanda,
            'jawabanEsai' => $jawabanEsai,
            'totalResponden' => $totalResponden,
            'totalJawaban' => $totalJawaban,
            'rataRataKeseluruhan' => $rataRataKeseluruhan,
            'dosenFilterList' => $dosenFilterList,
            'filterNipy' => $filterNipy,
            'chartDistribusi' => $chartDistribusi,
            'chartPerPertanyaan' => $chartPerPertanyaan,
        ]);
    }
}
