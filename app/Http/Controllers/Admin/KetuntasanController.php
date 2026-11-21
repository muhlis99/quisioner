<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\JatahMakul;
use App\Models\Ketuntasan;
use App\Models\Mahasiswa;
use App\Models\Quisioner;
use App\Models\Skor;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KetuntasanController extends Controller
{
    /**
     * Rekapitulasi Monitoring Ketuntasan, Total Skor, Rata-rata Skor,
     * Konversi Nilai (0–100), dan Kategori Kelayakan (Sesuai / Tidak Sesuai) per-mahasiswa.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $tahunAjaran = trim((string) $request->query('tahun_ajaran', ''));
        $quisionerId = trim((string) $request->query('id_quisioner', ''));
        $semester = (string) $request->query('semester', '');
        $status = (string) $request->query('ketuntasan', '');
        $keteranganFilter = (string) $request->query('keterangan', '');
        $sort = (string) $request->query('sort', 'nim_asc');
        $perPageOptions = ['10', '25', '50', '100', 'all'];
        $perPage = (string) $request->query('per_page', '25');

        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = '25';
        }

        if (! in_array($semester, ['ganjil', 'genap'], true)) {
            $semester = '';
        }
        if (! in_array($status, ['0', '1'], true)) {
            $status = '';
        }
        if (! in_array($keteranganFilter, ['sesuai', 'tidak_sesuai', 'belum_dinilai'], true)) {
            $keteranganFilter = '';
        }
        if (! in_array($sort, ['nim_asc', 'nama_asc', 'skor_desc', 'rata_desc', 'rata_asc', 'tuntas_desc'], true)) {
            $sort = 'nim_asc';
        }

        // Ambil nilai skor maksimal dari tb_skor (fallback ke 5 jika kosong)
        $maxSkor = $this->resolveMaxSkor();

        // Hitung jumlah quisioner aktif pada cakupan filter (acuan status Tuntas per-mahasiswa)
        $totalQuisionerWajib = Quisioner::aktif()
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('semester', $semester))
            ->when($quisionerId !== '' && ctype_digit($quisionerId), fn ($q) => $q->where('id', (int) $quisionerId))
            ->count();

        // Base query per-mahasiswa dengan agregasi subquery SQL (bebas N+1, kompatibel MariaDB & SQLite)
        $baseQuery = $this->buildRekapBaseQuery($search, $tahunAjaran, $semester, $quisionerId);

        // Ringkasan KPI global sesuai filter pencarian & periode
        $summaryQuery = clone $baseQuery;
        $summaryQuery->selectRaw('COUNT(*) as total_mahasiswa');
        if ($totalQuisionerWajib > 0) {
            $summaryQuery->selectRaw(
                'COALESCE(SUM(CASE WHEN COALESCE(kt.jumlah_tuntas, 0) >= ? THEN 1 ELSE 0 END), 0) as total_tuntas',
                [$totalQuisionerWajib]
            );
        } else {
            $summaryQuery->selectRaw('0 as total_tuntas');
        }
        $summaryQuery->selectRaw('COALESCE(SUM(COALESCE(hs.total_skor, 0)), 0) as grand_total_skor');
        $summaryQuery->selectRaw('COALESCE(SUM(COALESCE(hs.jumlah_jawaban, 0)), 0) as grand_total_jawaban');
        $summaryQuery->selectRaw(
            'COALESCE(SUM(CASE WHEN COALESCE(hs.jumlah_jawaban, 0) > 0 AND ROUND((COALESCE(hs.rata_rata_skor, 0) / ?) * 100) >= 80 THEN 1 ELSE 0 END), 0) as total_sesuai',
            [$maxSkor]
        );
        $summaryQuery->selectRaw(
            'COALESCE(SUM(CASE WHEN COALESCE(hs.jumlah_jawaban, 0) > 0 AND ROUND((COALESCE(hs.rata_rata_skor, 0) / ?) * 100) < 80 THEN 1 ELSE 0 END), 0) as total_tidak_sesuai',
            [$maxSkor]
        );

        $summary = $summaryQuery->first();

        $totalMahasiswa = (int) ($summary->total_mahasiswa ?? 0);
        $totalTuntas = (int) ($summary->total_tuntas ?? 0);
        $totalBelum = max(0, $totalMahasiswa - $totalTuntas);
        $persentase = $totalMahasiswa > 0 ? round(($totalTuntas / $totalMahasiswa) * 100, 1) : 0.0;
        $grandTotalSkor = (int) ($summary->grand_total_skor ?? 0);
        $grandTotalJawaban = (int) ($summary->grand_total_jawaban ?? 0);
        $rataRataKeseluruhan = $grandTotalJawaban > 0
            ? round($grandTotalSkor / $grandTotalJawaban, 2)
            : 0.0;
        $nilaiSkala100Keseluruhan = $grandTotalJawaban > 0
            ? $this->convertToNilaiSkala100($rataRataKeseluruhan, $maxSkor)
            : null;
        $kelayakanKeseluruhan = $this->resolveKelayakan($nilaiSkala100Keseluruhan);
        $totalSesuai = (int) ($summary->total_sesuai ?? 0);
        $totalTidakSesuai = (int) ($summary->total_tidak_sesuai ?? 0);

        // Query tabel per-mahasiswa dengan filter status, keterangan kelayakan, & sorting
        $tableQuery = (clone $baseQuery)->select(
            'tb_mahasiswa.id',
            'tb_mahasiswa.nim',
            'tb_mahasiswa.nama',
            DB::raw('COALESCE(kt.jumlah_tuntas, 0) as jumlah_tuntas'),
            DB::raw('COALESCE(kt.jumlah_tercatat, 0) as jumlah_tercatat'),
            DB::raw('COALESCE(hs.total_skor, 0) as total_skor'),
            DB::raw('COALESCE(hs.jumlah_jawaban, 0) as jumlah_jawaban'),
            DB::raw('COALESCE(hs.rata_rata_skor, 0) as rata_rata_skor')
        );

        if ($status === '1') {
            if ($totalQuisionerWajib > 0) {
                $tableQuery->whereRaw('COALESCE(kt.jumlah_tuntas, 0) >= ?', [$totalQuisionerWajib]);
            } else {
                $tableQuery->whereRaw('1 = 0');
            }
        } elseif ($status === '0' && $totalQuisionerWajib > 0) {
            $tableQuery->whereRaw('COALESCE(kt.jumlah_tuntas, 0) < ?', [$totalQuisionerWajib]);
        }

        if ($keteranganFilter === 'sesuai') {
            $tableQuery->whereRaw(
                'COALESCE(hs.jumlah_jawaban, 0) > 0 AND ROUND((COALESCE(hs.rata_rata_skor, 0) / ?) * 100) >= 80',
                [$maxSkor]
            );
        } elseif ($keteranganFilter === 'tidak_sesuai') {
            $tableQuery->whereRaw(
                'COALESCE(hs.jumlah_jawaban, 0) > 0 AND ROUND((COALESCE(hs.rata_rata_skor, 0) / ?) * 100) < 80',
                [$maxSkor]
            );
        } elseif ($keteranganFilter === 'belum_dinilai') {
            $tableQuery->whereRaw('COALESCE(hs.jumlah_jawaban, 0) = 0');
        }

        match ($sort) {
            'nama_asc' => $tableQuery->orderBy('tb_mahasiswa.nama'),
            'skor_desc' => $tableQuery->orderByDesc(DB::raw('COALESCE(hs.total_skor, 0)'))->orderBy('tb_mahasiswa.nim'),
            'rata_desc' => $tableQuery->orderByDesc(DB::raw('COALESCE(hs.rata_rata_skor, 0)'))->orderBy('tb_mahasiswa.nim'),
            'rata_asc' => $tableQuery->orderByRaw('CASE WHEN COALESCE(hs.jumlah_jawaban, 0) > 0 THEN 0 ELSE 1 END')
                ->orderBy(DB::raw('COALESCE(hs.rata_rata_skor, 0)'))
                ->orderBy('tb_mahasiswa.nim'),
            'tuntas_desc' => $tableQuery->orderByDesc(DB::raw('COALESCE(kt.jumlah_tuntas, 0)'))->orderBy('tb_mahasiswa.nim'),
            default => $tableQuery->orderBy('tb_mahasiswa.nim'),
        };

        // Hitung total baris hasil filter terlebih dahulu agar per_page=all & Jump to Page selalu akurat
        $filteredTotal = (clone $tableQuery)->count();
        if ($perPage === 'all') {
            $effectivePerPage = max(1, $filteredTotal);
            $currentPage = 1;
        } else {
            $effectivePerPage = (int) $perPage;
            $maxPage = max(1, (int) ceil($filteredTotal / $effectivePerPage));
            $requestedPage = (int) $request->query('page', 1);
            $currentPage = min($maxPage, max(1, $requestedPage));
        }

        $rekapMahasiswa = $tableQuery
            ->paginate($effectivePerPage, ['*'], 'page', $currentPage)
            ->withQueryString();

        // Petakan nilai skala 0–100 (bulat, terkunci 0..100) & keterangan kelayakan pada setiap baris
        $rekapMahasiswa->getCollection()->transform(function ($row) use ($totalQuisionerWajib, $maxSkor) {
            $jumlahTuntas = (int) $row->jumlah_tuntas;
            $jumlahJawaban = (int) $row->jumlah_jawaban;
            $totalSkor = (int) $row->total_skor;
            $rataRata = $jumlahJawaban > 0 ? round((float) $row->rata_rata_skor, 2) : 0.0;
            $nilaiAkhir = $jumlahJawaban > 0 ? $this->convertToNilaiSkala100((float) $row->rata_rata_skor, $maxSkor) : null;
            $kelayakan = $this->resolveKelayakan($nilaiAkhir);

            $isTuntas = $totalQuisionerWajib > 0 && $jumlahTuntas >= $totalQuisionerWajib;
            $isSebagian = ! $isTuntas && $jumlahTuntas > 0;
            $progresPersen = $totalQuisionerWajib > 0
                ? min(100, (int) round(($jumlahTuntas / $totalQuisionerWajib) * 100))
                : 0;

            $row->jumlah_tuntas = $jumlahTuntas;
            $row->total_wajib = $totalQuisionerWajib;
            $row->progres_persen = $progresPersen;
            $row->is_tuntas = $isTuntas;
            $row->is_sebagian = $isSebagian;
            $row->total_skor = $totalSkor;
            $row->jumlah_jawaban = $jumlahJawaban;
            $row->rata_rata_skor = $rataRata;
            $row->nilai_akhir = $nilaiAkhir;
            $row->kelayakan = $kelayakan;

            return $row;
        });

        $tahunAjaranList = Quisioner::aktif()
            ->select('tahun_ajaran')
            ->whereNotNull('tahun_ajaran')
            ->where('tahun_ajaran', '!=', '')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran');

        $quisionerList = Quisioner::aktif()
            ->orderByDesc('id')
            ->get(['id', 'judul_quisioner', 'tahun_ajaran', 'semester', 'type']);

        return view('admin.ketuntasan.index', [
            'rekapMahasiswa' => $rekapMahasiswa,
            'tahunAjaranList' => $tahunAjaranList,
            'quisionerList' => $quisionerList,
            'search' => $search,
            'tahunAjaran' => $tahunAjaran,
            'quisionerId' => $quisionerId,
            'semester' => $semester,
            'status' => $status,
            'keteranganFilter' => $keteranganFilter,
            'sort' => $sort,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'maxSkor' => $maxSkor,
            'totalQuisionerWajib' => $totalQuisionerWajib,
            'totalMahasiswa' => $totalMahasiswa,
            'totalTuntas' => $totalTuntas,
            'totalBelum' => $totalBelum,
            'persentase' => $persentase,
            'grandTotalSkor' => $grandTotalSkor,
            'grandTotalJawaban' => $grandTotalJawaban,
            'rataRataKeseluruhan' => $rataRataKeseluruhan,
            'nilaiSkala100Keseluruhan' => $nilaiSkala100Keseluruhan,
            'kelayakanKeseluruhan' => $kelayakanKeseluruhan,
            'totalSesuai' => $totalSesuai,
            'totalTidakSesuai' => $totalTidakSesuai,
        ]);
    }

    /**
     * Halaman Detail Ketuntasan, Konversi Nilai (0–100), & Kategori Kelayakan per-mahasiswa.
     */
    public function show(Request $request, string $nim): View
    {
        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();

        $tahunAjaran = trim((string) $request->query('tahun_ajaran', ''));
        $quisionerId = trim((string) $request->query('id_quisioner', ''));
        $semester = (string) $request->query('semester', '');

        if (! in_array($semester, ['ganjil', 'genap'], true)) {
            $semester = '';
        }

        $maxSkor = $this->resolveMaxSkor();

        $quisioners = Quisioner::aktif()
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('semester', $semester))
            ->when($quisionerId !== '' && ctype_digit($quisionerId), fn ($q) => $q->where('id', (int) $quisionerId))
            ->orderByDesc('id')
            ->get();

        $qIds = $quisioners->pluck('id')->all();

        $ketuntasanMap = Ketuntasan::where('niim', $nim)
            ->whereIn('id_quisioner', empty($qIds) ? [-1] : $qIds)
            ->get()
            ->keyBy('id_quisioner');

        // Agregasi skor per quisioner untuk mahasiswa ini (khusus soal pilihan ganda skor > 0)
        $skorPerQuisioner = empty($qIds)
            ? collect()
            : DB::table('tb_hasil')
                ->join('tb_pertanyaan', 'tb_pertanyaan.id', '=', 'tb_hasil.id_pertanyaan')
                ->where('tb_hasil.nim', $nim)
                ->whereIn('tb_pertanyaan.id_quisioner', $qIds)
                ->whereIn('tb_pertanyaan.type', ['ganda', 'pilihan'])
                ->whereNotNull('tb_hasil.skor')
                ->where('tb_hasil.skor', '>', 0)
                ->select(
                    'tb_pertanyaan.id_quisioner',
                    DB::raw('COALESCE(SUM(tb_hasil.skor), 0) as total_skor'),
                    DB::raw('COUNT(tb_hasil.id) as jumlah_jawaban'),
                    DB::raw('COALESCE(AVG(tb_hasil.skor), 0) as rata_rata')
                )
                ->groupBy('tb_pertanyaan.id_quisioner')
                ->get()
                ->keyBy('id_quisioner');

        $detailQuisioner = $quisioners->map(function (Quisioner $q) use ($ketuntasanMap, $skorPerQuisioner, $maxSkor) {
            $kt = $ketuntasanMap->get($q->id);
            $sk = $skorPerQuisioner->get($q->id);
            $jumlahJawaban = (int) ($sk->jumlah_jawaban ?? 0);
            $totalSkor = (int) ($sk->total_skor ?? 0);
            $rataRataRaw = $jumlahJawaban > 0 ? (float) $sk->rata_rata : 0.0;
            $rataRata = $jumlahJawaban > 0 ? round($rataRataRaw, 2) : 0.0;
            $nilaiAkhir = $jumlahJawaban > 0 ? $this->convertToNilaiSkala100($rataRataRaw, $maxSkor) : null;

            return [
                'quisioner' => $q,
                'ketuntasan' => $kt,
                'is_tuntas' => (bool) ($kt && $kt->ketuntasan === '1'),
                'total_skor' => $totalSkor,
                'jumlah_jawaban' => $jumlahJawaban,
                'rata_rata' => $rataRata,
                'nilai_akhir' => $nilaiAkhir,
                'kelayakan' => $this->resolveKelayakan($nilaiAkhir),
            ];
        });

        $totalWajib = $detailQuisioner->count();
        $jumlahTuntas = $detailQuisioner->where('is_tuntas', true)->count();
        $totalSkorKeseluruhan = (int) $detailQuisioner->sum('total_skor');
        $totalJawabanKeseluruhan = (int) $detailQuisioner->sum('jumlah_jawaban');
        $rataRataKeseluruhanRaw = $totalJawabanKeseluruhan > 0
            ? ($totalSkorKeseluruhan / $totalJawabanKeseluruhan)
            : 0.0;
        $rataRataKeseluruhan = $totalJawabanKeseluruhan > 0
            ? round($rataRataKeseluruhanRaw, 2)
            : 0.0;
        $nilaiAkhirKeseluruhan = $totalJawabanKeseluruhan > 0
            ? $this->convertToNilaiSkala100($rataRataKeseluruhanRaw, $maxSkor)
            : null;
        $kelayakanKeseluruhan = $this->resolveKelayakan($nilaiAkhirKeseluruhan);

        // Seluruh rincian jawaban mahasiswa pada cakupan filter (tanpa limit)
        $rawJawaban = empty($qIds)
            ? collect()
            : DB::table('tb_hasil')
                ->join('tb_pertanyaan', 'tb_pertanyaan.id', '=', 'tb_hasil.id_pertanyaan')
                ->join('tb_quisioner', 'tb_quisioner.id', '=', 'tb_pertanyaan.id_quisioner')
                ->where('tb_hasil.nim', $nim)
                ->whereIn('tb_pertanyaan.id_quisioner', $qIds)
                ->select(
                    'tb_hasil.id',
                    'tb_hasil.tanggal',
                    'tb_hasil.kode_makul',
                    'tb_hasil.nipy',
                    'tb_hasil.isi_pertanyaan',
                    'tb_hasil.skor',
                    'tb_hasil.isi_jawaban',
                    'tb_pertanyaan.id_quisioner',
                    'tb_pertanyaan.kategori',
                    'tb_pertanyaan.type as tipe_pertanyaan',
                    'tb_quisioner.judul_quisioner',
                    'tb_quisioner.type as tipe_quisioner'
                )
                ->orderByDesc('tb_pertanyaan.id_quisioner')
                ->orderBy('tb_pertanyaan.kategori')
                ->orderBy('tb_hasil.id')
                ->get();

        // Lookup batch bebas N+1: tb_skor, tb_dosen, tb_jatah_makul
        $skorMap = Skor::orderBy('skor')->get()->keyBy('skor');

        $usedNipys = $rawJawaban
            ->pluck('nipy')
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();

        $dosenMap = empty($usedNipys)
            ? []
            : Dosen::whereIn('nipy', $usedNipys)
                ->pluck('nama', 'nipy')
                ->all();

        $makulStudentRows = JatahMakul::where('nim', $nim)
            ->get(['kode_makul', 'nama_makul', 'nipy', 'nama_dosen']);

        $makulByPair = [];
        $makulByKode = [];
        foreach ($makulStudentRows as $m) {
            $kode = trim((string) $m->kode_makul);
            $nipyM = trim((string) $m->nipy);
            if ($kode !== '') {
                $makulByPair["{$kode}|{$nipyM}"] = [
                    'nama_makul' => (string) $m->nama_makul,
                    'nama_dosen' => (string) $m->nama_dosen,
                ];
                if (! isset($makulByKode[$kode])) {
                    $makulByKode[$kode] = [
                        'nama_makul' => (string) $m->nama_makul,
                        'nama_dosen' => (string) $m->nama_dosen,
                    ];
                }
            }
        }

        // Fallback nama_makul jika kode_makul di tb_hasil tidak tercatat pada jatah_makul NIM ini
        $missingKodes = $rawJawaban
            ->pluck('kode_makul')
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '' && ! isset($makulByKode[$v]))
            ->unique()
            ->values()
            ->all();

        if (! empty($missingKodes)) {
            $extraMakul = JatahMakul::whereIn('kode_makul', $missingKodes)
                ->select('kode_makul', 'nama_makul')
                ->distinct()
                ->pluck('nama_makul', 'kode_makul')
                ->all();
            foreach ($extraMakul as $kode => $namaMakul) {
                $makulByKode[trim((string) $kode)] = [
                    'nama_makul' => (string) $namaMakul,
                    'nama_dosen' => '',
                ];
            }
        }

        // Perkaya setiap entri jawaban dengan konteks EDOM, huruf/keterangan tb_skor, & badge warna
        $rincianJawaban = $rawJawaban->map(function ($ans) use ($skorMap, $dosenMap, $makulByPair, $makulByKode, $maxSkor) {
            $isPilihan = in_array(strtolower((string) $ans->tipe_pertanyaan), ['ganda', 'pilihan'], true);
            $kategori = trim((string) $ans->kategori) !== '' ? trim((string) $ans->kategori) : 'Umum';
            $kodeMakul = trim((string) $ans->kode_makul);
            $nipy = trim((string) $ans->nipy);

            $pairInfo = $makulByPair["{$kodeMakul}|{$nipy}"] ?? null;
            $kodeInfo = $makulByKode[$kodeMakul] ?? null;

            $namaDosen = $dosenMap[$nipy]
                ?? ($pairInfo['nama_dosen'] ?? '')
                ?: ($kodeInfo['nama_dosen'] ?? '');
            $namaMakul = $pairInfo['nama_makul']
                ?? ($kodeInfo['nama_makul'] ?? '');

            $skorInt = (int) ($ans->skor ?? 0);
            $metaSkor = $isPilihan ? $skorMap->get($skorInt) : null;
            $nilai100 = ($isPilihan && $skorInt > 0)
                ? $this->convertToNilaiSkala100((float) $skorInt, $maxSkor)
                : null;
            $kelayakanSkor = $this->resolveKelayakan($nilai100);

            $tanggalDisplay = '-';
            if (! empty($ans->tanggal)) {
                try {
                    $tanggalDisplay = \Illuminate\Support\Carbon::parse($ans->tanggal)->translatedFormat('d M Y');
                } catch (\Throwable) {
                    $tanggalDisplay = (string) $ans->tanggal;
                }
            }

            $ans->is_pilihan = $isPilihan;
            $ans->tipe_key = $isPilihan ? 'pilihan' : 'esai';
            $ans->tipe_label = $isPilihan ? 'Pilihan' : 'Isian';
            $ans->kategori_clean = $kategori;
            $ans->kode_makul_clean = $kodeMakul;
            $ans->nipy_clean = $nipy;
            $ans->nama_dosen = $namaDosen;
            $ans->nama_makul = $namaMakul;
            $ans->huruf_skor = $metaSkor?->huruf ?? '';
            $ans->keterangan_skor = $metaSkor?->keterangan ?? (string) $ans->isi_jawaban;
            $ans->nilai_100 = $nilai100;
            $ans->kelayakan_skor = $kelayakanSkor;
            $ans->tanggal_display = $tanggalDisplay;

            return $ans;
        });

        // Hitung total semua entri jawaban (pilihan + esai) per quisioner untuk tombol Lihat Jawaban & Tab
        $jawabanCountByQuisioner = $rincianJawaban
            ->groupBy('id_quisioner')
            ->map(fn ($items) => $items->count())
            ->all();

        $detailQuisioner = $detailQuisioner->map(function (array $item) use ($jawabanCountByQuisioner) {
            $qId = $item['quisioner']->id;
            $item['total_entri_jawaban'] = (int) ($jawabanCountByQuisioner[$qId] ?? 0);

            return $item;
        });

        // Susun ringkasan skor per kategori (skala 0–100) untuk Tab "Semua" & tiap Tab Quisioner
        $buildKategoriSummary = function ($rows) use ($maxSkor): array {
            return $rows
                ->filter(fn ($r) => $r->is_pilihan && (int) $r->skor > 0)
                ->groupBy('kategori_clean')
                ->map(function ($group, $kat) use ($maxSkor) {
                    $avgRaw = (float) $group->avg('skor');
                    $nilai100 = $this->convertToNilaiSkala100($avgRaw, $maxSkor);

                    return [
                        'kategori' => (string) $kat,
                        'rata_skor' => round($avgRaw, 2),
                        'nilai_100' => $nilai100,
                        'jumlah' => $group->count(),
                        'kelayakan' => $this->resolveKelayakan($nilai100),
                    ];
                })
                ->values()
                ->all();
        };

        $jawabanTabs = [];
        $jawabanTabs[] = [
            'key' => 'all',
            'label' => 'Semua Quisioner',
            'type' => null,
            'count' => $rincianJawaban->count(),
            'kategori_summary' => $buildKategoriSummary($rincianJawaban),
            'kategori_list' => $rincianJawaban->pluck('kategori_clean')->unique()->sort()->values()->all(),
        ];

        foreach ($detailQuisioner as $dq) {
            $q = $dq['quisioner'];
            $rowsQ = $rincianJawaban->where('id_quisioner', $q->id)->values();
            if ($rowsQ->isEmpty()) {
                continue;
            }
            $jawabanTabs[] = [
                'key' => (string) $q->id,
                'label' => $q->judul_quisioner,
                'type' => strtoupper((string) $q->type),
                'count' => $rowsQ->count(),
                'kategori_summary' => $buildKategoriSummary($rowsQ),
                'kategori_list' => $rowsQ->pluck('kategori_clean')->unique()->sort()->values()->all(),
            ];
        }

        return view('admin.ketuntasan.show', [
            'mahasiswa' => $mahasiswa,
            'detailQuisioner' => $detailQuisioner,
            'rincianJawaban' => $rincianJawaban,
            'jawabanTabs' => $jawabanTabs,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'quisionerId' => $quisionerId,
            'maxSkor' => $maxSkor,
            'totalWajib' => $totalWajib,
            'jumlahTuntas' => $jumlahTuntas,
            'totalSkorKeseluruhan' => $totalSkorKeseluruhan,
            'totalJawabanKeseluruhan' => $totalJawabanKeseluruhan,
            'rataRataKeseluruhan' => $rataRataKeseluruhan,
            'nilaiAkhirKeseluruhan' => $nilaiAkhirKeseluruhan,
            'kelayakanKeseluruhan' => $kelayakanKeseluruhan,
        ]);
    }

    public function destroy(Ketuntasan $ketuntasan): RedirectResponse
    {
        $nim = $ketuntasan->niim;
        $ketuntasan->delete();

        return redirect()
            ->route('admin.ketuntasan.show', $nim)
            ->with('success', 'Catatan ketuntasan berhasil dihapus.');
    }

    /**
     * Membangun base query per-mahasiswa yang menggabungkan agregasi tb_ketuntasan dan tb_hasil.
     */
    private function buildRekapBaseQuery(
        string $search,
        string $tahunAjaran,
        string $semester,
        string $quisionerId
    ): Builder {
        $ktAgg = DB::table('tb_ketuntasan')
            ->join('tb_quisioner', 'tb_quisioner.id', '=', 'tb_ketuntasan.id_quisioner')
            ->where('tb_quisioner.hapus', '0')
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tb_quisioner.tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('tb_quisioner.semester', $semester))
            ->when($quisionerId !== '' && ctype_digit($quisionerId), fn ($q) => $q->where('tb_quisioner.id', (int) $quisionerId))
            ->select(
                'tb_ketuntasan.niim',
                DB::raw("COUNT(DISTINCT CASE WHEN tb_ketuntasan.ketuntasan = '1' THEN tb_ketuntasan.id_quisioner END) as jumlah_tuntas"),
                DB::raw('COUNT(DISTINCT tb_ketuntasan.id_quisioner) as jumlah_tercatat')
            )
            ->groupBy('tb_ketuntasan.niim');

        $hsAgg = DB::table('tb_hasil')
            ->join('tb_pertanyaan', 'tb_pertanyaan.id', '=', 'tb_hasil.id_pertanyaan')
            ->join('tb_quisioner', 'tb_quisioner.id', '=', 'tb_pertanyaan.id_quisioner')
            ->where('tb_quisioner.hapus', '0')
            ->whereIn('tb_pertanyaan.type', ['ganda', 'pilihan'])
            ->whereNotNull('tb_hasil.skor')
            ->where('tb_hasil.skor', '>', 0)
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tb_quisioner.tahun_ajaran', $tahunAjaran))
            ->when($semester !== '', fn ($q) => $q->where('tb_quisioner.semester', $semester))
            ->when($quisionerId !== '' && ctype_digit($quisionerId), fn ($q) => $q->where('tb_quisioner.id', (int) $quisionerId))
            ->select(
                'tb_hasil.nim',
                DB::raw('COALESCE(SUM(tb_hasil.skor), 0) as total_skor'),
                DB::raw('COUNT(tb_hasil.id) as jumlah_jawaban'),
                DB::raw('COALESCE(AVG(tb_hasil.skor), 0) as rata_rata_skor')
            )
            ->groupBy('tb_hasil.nim');

        return DB::table('tb_mahasiswa')
            ->leftJoinSub($ktAgg, 'kt', fn ($join) => $join->on('kt.niim', '=', 'tb_mahasiswa.nim'))
            ->leftJoinSub($hsAgg, 'hs', fn ($join) => $join->on('hs.nim', '=', 'tb_mahasiswa.nim'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('tb_mahasiswa.nim', 'like', "%{$search}%")
                        ->orWhere('tb_mahasiswa.nama', 'like', "%{$search}%");
                });
            });
    }

    /**
     * Ambil nilai skor maksimum dari tb_skor (fallback ke 5 jika tabel kosong).
     */
    private function resolveMaxSkor(): float
    {
        $max = DB::table('tb_skor')->max('skor');
        $numeric = is_numeric($max) ? (float) $max : 0.0;

        return $numeric > 0 ? $numeric : 5.0;
    }

    /**
     * Konversi rata-rata skor pilihan ganda ke skala bilangan bulat 0–100 (terkunci mutlak di [0, 100]).
     */
    private function convertToNilaiSkala100(float $rataRataSkor, float $maxSkor): int
    {
        $nilaiAkhir = (int) round(($rataRataSkor / $maxSkor) * 100);

        return min(100, max(0, $nilaiAkhir));
    }

    /**
     * Menentukan kategori kelayakan nilai & gaya badge Tailwind:
     * - 80–100: Sesuai (hijau emerald)
     * - < 80  : Tidak Sesuai (merah lembut)
     * - null  : Belum Dinilai (abu-abu)
     *
     * @return array{key: string, label: string, badge_class: string, value_class: string}
     */
    private function resolveKelayakan(?int $nilaiAkhir): array
    {
        if ($nilaiAkhir === null) {
            return [
                'key' => 'belum_dinilai',
                'label' => 'Belum Dinilai',
                'badge_class' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
                'value_class' => 'text-slate-400',
            ];
        }

        if ($nilaiAkhir >= 80) {
            return [
                'key' => 'sesuai',
                'label' => 'Sesuai',
                'badge_class' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
                'value_class' => 'text-[#0B6B3A]',
            ];
        }

        return [
            'key' => 'tidak_sesuai',
            'label' => 'Tidak Sesuai',
            'badge_class' => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
            'value_class' => 'text-rose-700',
        ];
    }
}
