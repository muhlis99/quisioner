<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Quisioner;
use App\Models\Skor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tahunAjaranList = Quisioner::aktif()
            ->select('tahun_ajaran')
            ->whereNotNull('tahun_ajaran')
            ->where('tahun_ajaran', '!=', '')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->merge(
                DB::table('tb_ketuntasan')
                    ->select('tahun_ajaran')
                    ->whereNotNull('tahun_ajaran')
                    ->where('tahun_ajaran', '!=', '')
                    ->distinct()
                    ->pluck('tahun_ajaran')
            )
            ->unique()
            ->sortDesc()
            ->values();

        $initialPayload = $this->buildDashboardPayload('', '', '');

        return view('admin.dashboard', [
            'tahunAjaranList' => $tahunAjaranList,
            'initialPayload' => $initialPayload,
            'totalMahasiswa' => $initialPayload['kpi']['total_mahasiswa'],
            'totalDosen' => $initialPayload['kpi']['total_dosen'],
            'totalQuisioner' => $initialPayload['kpi']['total_quisioner'],
            'totalTuntas' => $initialPayload['kpi']['total_tuntas'],
        ]);
    }

    /**
     * Endpoint JSON teragregasi untuk memperbarui KPI & 3 grafik dashboard via AJAX/Fetch API.
     */
    public function getChartData(Request $request): JsonResponse
    {
        $tahunAjaran = trim((string) $request->query('tahun_ajaran', ''));
        $semester = (string) $request->query('semester', '');
        $type = (string) $request->query('type', '');

        if (! in_array($semester, ['ganjil', 'genap'], true)) {
            $semester = '';
        }

        if (! in_array($type, ['edom', 'biasa'], true)) {
            $type = '';
        }

        return response()->json(
            $this->buildDashboardPayload($tahunAjaran, $semester, $type)
        );
    }

    /**
     * Menghitung seluruh KPI dan data 3 grafik secara efisien dengan GROUP BY.
     *
     * @return array<string, mixed>
     */
    private function buildDashboardPayload(string $tahunAjaran, string $semester, string $type): array
    {
        // 1. KPI Dasar
        $totalMahasiswa = Mahasiswa::count();
        $totalDosen = Dosen::count();

        $totalQuisioner = Quisioner::aktif()
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('semester', $semester))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->count();

        // 2. Grafik 2 & KPI Ketuntasan (GROUP BY tb_ketuntasan.ketuntasan)
        $ketuntasanRows = DB::table('tb_ketuntasan')
            ->join('tb_quisioner', 'tb_quisioner.id', '=', 'tb_ketuntasan.id_quisioner')
            ->where('tb_quisioner.hapus', '0')
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tb_ketuntasan.tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('tb_ketuntasan.semester', $semester))
            ->when($type !== '', fn ($q) => $q->where('tb_quisioner.type', $type))
            ->select('tb_ketuntasan.ketuntasan', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tb_ketuntasan.ketuntasan')
            ->pluck('jumlah', 'ketuntasan');

        $jumlahTuntas = (int) ($ketuntasanRows['1'] ?? 0);
        $jumlahBelumTuntas = (int) ($ketuntasanRows['0'] ?? 0);
        $totalKetuntasan = $jumlahTuntas + $jumlahBelumTuntas;
        $persentaseKetuntasan = $totalKetuntasan > 0
            ? round(($jumlahTuntas / $totalKetuntasan) * 100, 1)
            : 0.0;

        // 3. Grafik 1 — Distribusi Skor Jawaban (GROUP BY tb_hasil.skor, khusus pertanyaan pilihan ganda)
        $skorRows = DB::table('tb_hasil')
            ->join('tb_pertanyaan', 'tb_pertanyaan.id', '=', 'tb_hasil.id_pertanyaan')
            ->join('tb_quisioner', 'tb_quisioner.id', '=', 'tb_pertanyaan.id_quisioner')
            ->where('tb_quisioner.hapus', '0')
            ->whereIn('tb_pertanyaan.type', ['ganda', 'pilihan'])
            ->whereNotNull('tb_hasil.skor')
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tb_quisioner.tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('tb_quisioner.semester', $semester))
            ->when($type !== '', fn ($q) => $q->where('tb_quisioner.type', $type))
            ->select('tb_hasil.skor', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tb_hasil.skor')
            ->orderBy('tb_hasil.skor')
            ->get();

        $masterSkor = Skor::orderBy('skor')->get()->keyBy('skor');
        $skorCounts = [];
        foreach ($skorRows as $row) {
            $skorCounts[(int) $row->skor] = (int) $row->jumlah;
        }

        // Gabungkan skala master tb_skor dengan skor aktual yang ada di tb_hasil
        $allSkorKeys = collect($masterSkor->keys()->all())
            ->merge(array_keys($skorCounts))
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->sort()
            ->values();

        $totalSkorJawaban = array_sum($skorCounts);
        $distLabels = [];
        $distKeterangan = [];
        $distData = [];
        $distPercentages = [];

        foreach ($allSkorKeys as $skorVal) {
            $meta = $masterSkor->get($skorVal);
            $ket = $meta ? $meta->keterangan : "Skor {$skorVal}";
            $huruf = $meta ? " ({$meta->huruf})" : '';
            $cnt = $skorCounts[$skorVal] ?? 0;

            $distLabels[] = "Skor {$skorVal}{$huruf}";
            $distKeterangan[] = $ket;
            $distData[] = $cnt;
            $distPercentages[] = $totalSkorJawaban > 0 ? round(($cnt / $totalSkorJawaban) * 100, 1) : 0.0;
        }

        // 4. Grafik 3 — Tren Partisipasi Harian (GROUP BY tb_hasil.tanggal)
        $trenRows = DB::table('tb_hasil')
            ->join('tb_pertanyaan', 'tb_pertanyaan.id', '=', 'tb_hasil.id_pertanyaan')
            ->join('tb_quisioner', 'tb_quisioner.id', '=', 'tb_pertanyaan.id_quisioner')
            ->where('tb_quisioner.hapus', '0')
            ->whereNotNull('tb_hasil.tanggal')
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tb_quisioner.tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('tb_quisioner.semester', $semester))
            ->when($type !== '', fn ($q) => $q->where('tb_quisioner.type', $type))
            ->select(
                DB::raw('DATE(tb_hasil.tanggal) as tgl'),
                DB::raw('COUNT(tb_hasil.id) as total_jawaban'),
                DB::raw('COUNT(DISTINCT tb_hasil.nim) as total_responden')
            )
            ->groupBy(DB::raw('DATE(tb_hasil.tanggal)'))
            ->orderBy(DB::raw('DATE(tb_hasil.tanggal)'))
            ->get();

        $trenLabels = [];
        $trenJawaban = [];
        $trenResponden = [];

        foreach ($trenRows as $row) {
            $trenLabels[] = (string) $row->tgl;
            $trenJawaban[] = (int) $row->total_jawaban;
            $trenResponden[] = (int) $row->total_responden;
        }

        return [
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'type' => $type,
            ],
            'kpi' => [
                'total_mahasiswa' => $totalMahasiswa,
                'total_dosen' => $totalDosen,
                'total_quisioner' => $totalQuisioner,
                'total_tuntas' => $jumlahTuntas,
                'total_ketuntasan' => $totalKetuntasan,
                'persentase_ketuntasan' => $persentaseKetuntasan,
            ],
            'charts' => [
                'distribusi_skor' => [
                    'labels' => $distLabels,
                    'keterangan' => $distKeterangan,
                    'data' => $distData,
                    'percentages' => $distPercentages,
                    'total' => $totalSkorJawaban,
                    'has_data' => $totalSkorJawaban > 0,
                ],
                'ketuntasan' => [
                    'labels' => ['Sudah Tuntas', 'Belum Tuntas'],
                    'data' => [$jumlahTuntas, $jumlahBelumTuntas],
                    'total' => $totalKetuntasan,
                    'persentase_tuntas' => $persentaseKetuntasan,
                    'has_data' => $totalKetuntasan > 0,
                ],
                'tren_harian' => [
                    'labels' => $trenLabels,
                    'jawaban' => $trenJawaban,
                    'responden' => $trenResponden,
                    'total' => array_sum($trenJawaban),
                    'has_data' => count($trenLabels) > 0,
                ],
            ],
        ];
    }
}
