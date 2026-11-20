<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Hasil;
use App\Models\JatahMakul;
use App\Models\Ketuntasan;
use App\Models\Pertanyaan;
use App\Models\Quisioner;
use App\Models\Skor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuisionerController extends Controller
{
    /**
     * Halaman Daftar Quisioner Aktif (hapus = '0') beserta status ketuntasan & daftar makul EDOM.
     */
    public function index(Request $request): View
    {
        $nim = $this->currentNim($request);
        $filterType = (string) $request->query('type', '');

        $quisioners = Quisioner::aktif()
            ->withCount('pertanyaan')
            ->when(in_array($filterType, ['edom', 'biasa'], true), fn ($q) => $q->where('type', $filterType))
            ->orderByDesc('id')
            ->get();

        $ketuntasanMap = Ketuntasan::where('niim', $nim)
            ->get()
            ->keyBy('id_quisioner');

        $allJatahMakul = JatahMakul::where('nim', $nim)
            ->orderBy('nama_makul')
            ->get();

        // Pemetaan id_quisioner -> daftar id_pertanyaan untuk mengecek makul EDOM yang sudah diisi
        $quisionerIds = $quisioners->pluck('id')->all();
        $pertanyaanByQuisioner = Pertanyaan::whereIn('id_quisioner', empty($quisionerIds) ? [-1] : $quisionerIds)
            ->get(['id', 'id_quisioner'])
            ->groupBy('id_quisioner')
            ->map(fn ($items) => $items->pluck('id')->all());

        // Ambil pasangan (id_pertanyaan, kode_makul, nipy) yang sudah diisi mahasiswa ini di tb_hasil
        $allPertanyaanIds = $pertanyaanByQuisioner->flatten()->all();
        $hasilRows = empty($allPertanyaanIds)
            ? collect()
            : Hasil::where('nim', $nim)
                ->whereIn('id_pertanyaan', $allPertanyaanIds)
                ->get(['id_pertanyaan', 'kode_makul', 'nipy']);

        $items = $quisioners->map(function (Quisioner $q) use ($ketuntasanMap, $allJatahMakul, $pertanyaanByQuisioner, $hasilRows) {
            $ketuntasan = $ketuntasanMap->get($q->id);
            $qPertanyaanIds = $pertanyaanByQuisioner->get($q->id, []);
            $qHasil = $hasilRows->whereIn('id_pertanyaan', $qPertanyaanIds);

            $makulItems = collect();
            $selesaiMakul = 0;

            if ($q->type === 'edom') {
                $matchedMakuls = $allJatahMakul->filter(function (JatahMakul $m) use ($q) {
                    return trim((string) $m->tahun_ajaran) === trim((string) $q->tahun_ajaran)
                        && strtolower((string) $m->semester) === strtolower((string) $q->semester);
                })->values();

                // Fallback: jika tahun_ajaran di tb_jatah_makul berbeda format (misal belum difilter periode), tampilkan makul semester cocok atau semua makul milik NIM
                if ($matchedMakuls->isEmpty()) {
                    $matchedMakuls = $allJatahMakul->filter(
                        fn (JatahMakul $m) => strtolower((string) $m->semester) === strtolower((string) $q->semester)
                    )->values();
                }

                $makulItems = $matchedMakuls->map(function (JatahMakul $m) use ($qHasil) {
                    $sudahDiisi = $qHasil->contains(function ($h) use ($m) {
                        return trim((string) $h->kode_makul) === trim((string) $m->kode_makul)
                            && trim((string) $h->nipy) === trim((string) $m->nipy);
                    });

                    return [
                        'makul' => $m,
                        'sudah_diisi' => $sudahDiisi,
                    ];
                });

                $selesaiMakul = $makulItems->where('sudah_diisi', true)->count();
            }

            $isTuntas = ($ketuntasan && $ketuntasan->ketuntasan === '1')
                || ($q->type === 'edom' && $makulItems->isNotEmpty() && $selesaiMakul === $makulItems->count());

            return [
                'quisioner' => $q,
                'ketuntasan' => $ketuntasan,
                'is_tuntas' => $isTuntas,
                'makul_items' => $makulItems,
                'total_makul' => $makulItems->count(),
                'selesai_makul' => $selesaiMakul,
            ];
        });

        return view('mahasiswa.quisioner.index', [
            'items' => $items,
            'filterType' => $filterType,
        ]);
    }

    /**
     * Halaman Form Pengisian Quisioner.
     */
    public function show(Request $request, Quisioner $quisioner): View|RedirectResponse
    {
        abort_if($quisioner->hapus === '1', 404);

        $nim = $this->currentNim($request);
        $jatahMakul = null;

        if ($quisioner->type === 'edom') {
            $makulId = (int) $request->query('makul', 0);
            $jatahMakul = JatahMakul::where('id', $makulId)
                ->where('nim', $nim)
                ->first();

            if (! $jatahMakul) {
                return redirect()
                    ->route('mahasiswa.quisioner.index', ['type' => 'edom'])
                    ->with('error', 'Silakan pilih mata kuliah / dosen yang valid untuk mengisi kuisioner EDOM.');
            }

            if ($this->sudahIsiMakul($quisioner, $nim, $jatahMakul)) {
                return redirect()
                    ->route('mahasiswa.quisioner.index', ['type' => 'edom'])
                    ->with('warning', "Kuisioner EDOM untuk mata kuliah {$jatahMakul->nama_makul} ({$jatahMakul->nama_dosen}) sudah Anda isi.");
            }
        } else {
            $sudahTuntas = Ketuntasan::where('niim', $nim)
                ->where('id_quisioner', $quisioner->id)
                ->where('ketuntasan', '1')
                ->exists();

            if ($sudahTuntas) {
                return redirect()
                    ->route('mahasiswa.quisioner.index')
                    ->with('warning', "Quisioner '{$quisioner->judul_quisioner}' sudah selesai Anda isi.");
            }
        }

        $pertanyaans = $quisioner->pertanyaan()
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        if ($pertanyaans->isEmpty()) {
            return redirect()
                ->route('mahasiswa.quisioner.index')
                ->with('warning', 'Belum ada pertanyaan pada quisioner ini.');
        }

        $skors = Skor::orderBy('skor')->get();
        $pertanyaanPerKategori = $pertanyaans->groupBy('kategori');

        return view('mahasiswa.quisioner.form', [
            'quisioner' => $quisioner,
            'jatahMakul' => $jatahMakul,
            'pertanyaanPerKategori' => $pertanyaanPerKategori,
            'totalPertanyaan' => $pertanyaans->count(),
            'skors' => $skors,
        ]);
    }

    /**
     * Menyimpan seluruh jawaban ke tb_hasil dan memperbarui tb_ketuntasan dalam DB Transaction.
     */
    public function store(Request $request, Quisioner $quisioner): RedirectResponse
    {
        abort_if($quisioner->hapus === '1', 404);

        $nim = $this->currentNim($request);
        $jatahMakul = null;

        if ($quisioner->type === 'edom') {
            $makulId = (int) $request->input('jatah_makul_id', 0);
            $jatahMakul = JatahMakul::where('id', $makulId)
                ->where('nim', $nim)
                ->first();

            if (! $jatahMakul) {
                return redirect()
                    ->route('mahasiswa.quisioner.index', ['type' => 'edom'])
                    ->with('error', 'Data mata kuliah tidak valid untuk NIM Anda.');
            }

            if ($this->sudahIsiMakul($quisioner, $nim, $jatahMakul)) {
                return redirect()
                    ->route('mahasiswa.quisioner.index', ['type' => 'edom'])
                    ->with('warning', "Kuisioner EDOM untuk {$jatahMakul->nama_makul} sudah pernah dikirim.");
            }
        } else {
            $sudahTuntas = Ketuntasan::where('niim', $nim)
                ->where('id_quisioner', $quisioner->id)
                ->where('ketuntasan', '1')
                ->exists();

            if ($sudahTuntas) {
                return redirect()
                    ->route('mahasiswa.quisioner.index')
                    ->with('warning', 'Quisioner ini sudah pernah Anda selesaikan.');
            }
        }

        $pertanyaans = $quisioner->pertanyaan()
            ->orderBy('kategori')
            ->orderBy('id')
            ->get();

        if ($pertanyaans->isEmpty()) {
            return redirect()
                ->route('mahasiswa.quisioner.index')
                ->with('error', 'Tidak ada pertanyaan untuk disimpan.');
        }

        $skorMap = Skor::orderBy('skor')->get()->keyBy('skor');
        $validSkors = $skorMap->keys()->map(fn ($v) => (int) $v)->all();

        // Susun aturan validasi dinamis per pertanyaan
        $rules = [];
        $messages = [];

        foreach ($pertanyaans as $index => $p) {
            $nomor = $index + 1;
            $key = "jawaban.{$p->id}";

            if ($this->isPilihan($p->type)) {
                $rules[$key] = ['required', 'integer', Rule::in($validSkors)];
                $messages["{$key}.required"] = "Pertanyaan nomor {$nomor} wajib dipilih.";
                $messages["{$key}.in"] = "Pilihan skor pada pertanyaan nomor {$nomor} tidak valid.";
            } else {
                $rules[$key] = ['required', 'string', 'max:2000'];
                $messages["{$key}.required"] = "Jawaban isian pada pertanyaan nomor {$nomor} wajib diisi.";
            }
        }

        $validated = $request->validate($rules, $messages);
        $jawabanInput = $validated['jawaban'] ?? [];

        $tahunAjaranInt = $this->parseTahunAjaranInt((string) $quisioner->tahun_ajaran);
        $tanggalHariIni = now()->toDateString();

        DB::transaction(function () use (
            $quisioner,
            $pertanyaans,
            $jawabanInput,
            $skorMap,
            $nim,
            $jatahMakul,
            $tahunAjaranInt,
            $tanggalHariIni
        ) {
            foreach ($pertanyaans as $p) {
                $val = $jawabanInput[$p->id];

                if ($this->isPilihan($p->type)) {
                    $skorInt = (int) $val;
                    $keteranganSkor = (string) ($skorMap->get($skorInt)?->keterangan ?? '');

                    Hasil::create([
                        'tahun_ajaran' => $tahunAjaranInt,
                        'semester' => $quisioner->semester,
                        'tanggal' => $tanggalHariIni,
                        'type' => $quisioner->type,
                        'nipy' => $jatahMakul ? (string) $jatahMakul->nipy : '',
                        'nim' => $nim,
                        'id_pertanyaan' => $p->id,
                        'isi_pertanyaan' => $p->isi_pertanyaan,
                        'skor' => $skorInt,
                        'isi_jawaban' => $keteranganSkor,
                        'kode_makul' => $jatahMakul ? (string) $jatahMakul->kode_makul : '',
                    ]);
                } else {
                    Hasil::create([
                        'tahun_ajaran' => $tahunAjaranInt,
                        'semester' => $quisioner->semester,
                        'tanggal' => $tanggalHariIni,
                        'type' => $quisioner->type,
                        'nipy' => $jatahMakul ? (string) $jatahMakul->nipy : '',
                        'nim' => $nim,
                        'id_pertanyaan' => $p->id,
                        'isi_pertanyaan' => $p->isi_pertanyaan,
                        'skor' => 0,
                        'isi_jawaban' => trim((string) $val),
                        'kode_makul' => $jatahMakul ? (string) $jatahMakul->kode_makul : '',
                    ]);
                }
            }

            // Perbarui / simpan record pada tb_ketuntasan
            if ($quisioner->type === 'edom') {
                $makulPeriode = JatahMakul::where('nim', $nim)
                    ->where('tahun_ajaran', $quisioner->tahun_ajaran)
                    ->where('semester', $quisioner->semester)
                    ->get();

                if ($makulPeriode->isEmpty()) {
                    $makulPeriode = JatahMakul::where('nim', $nim)
                        ->where('semester', $quisioner->semester)
                        ->get();
                }

                $pertanyaanIds = $pertanyaans->pluck('id')->all();
                $totalTarget = max(1, $makulPeriode->count());
                $selesaiCount = 0;

                foreach ($makulPeriode as $m) {
                    $filled = Hasil::where('nim', $nim)
                        ->whereIn('id_pertanyaan', $pertanyaanIds)
                        ->where('kode_makul', $m->kode_makul)
                        ->where('nipy', $m->nipy)
                        ->exists();
                    if ($filled) {
                        $selesaiCount++;
                    }
                }

                $statusTuntas = $selesaiCount >= $totalTarget ? '1' : '0';

                Ketuntasan::updateOrCreate(
                    [
                        'id_quisioner' => $quisioner->id,
                        'niim' => $nim,
                    ],
                    [
                        'tahun_ajaran' => (string) $quisioner->tahun_ajaran,
                        'semester' => $quisioner->semester,
                        'status_insert_soal' => $totalTarget,
                        'status_penyelesaian' => $selesaiCount,
                        'ketuntasan' => $statusTuntas,
                    ]
                );
            } else {
                Ketuntasan::updateOrCreate(
                    [
                        'id_quisioner' => $quisioner->id,
                        'niim' => $nim,
                    ],
                    [
                        'tahun_ajaran' => (string) $quisioner->tahun_ajaran,
                        'semester' => $quisioner->semester,
                        'status_insert_soal' => $pertanyaans->count(),
                        'status_penyelesaian' => $pertanyaans->count(),
                        'ketuntasan' => '1',
                    ]
                );
            }
        });

        $pesan = $jatahMakul
            ? "Terima kasih! Evaluasi EDOM untuk mata kuliah {$jatahMakul->nama_makul} ({$jatahMakul->nama_dosen}) berhasil disimpan."
            : "Terima kasih! Jawaban quisioner '{$quisioner->judul_quisioner}' berhasil disimpan dan status Anda kini Tuntas.";

        return redirect()
            ->route('mahasiswa.quisioner.index')
            ->with('success', $pesan);
    }

    private function currentNim(Request $request): string
    {
        return (string) ($request->session()->get('mahasiswa_nim') ?? $request->user()->username);
    }

    private function isPilihan(string $type): bool
    {
        return in_array(strtolower($type), ['ganda', 'pilihan'], true);
    }

    private function sudahIsiMakul(Quisioner $quisioner, string $nim, JatahMakul $makul): bool
    {
        $pertanyaanIds = $quisioner->pertanyaan()->pluck('id')->all();
        if (empty($pertanyaanIds)) {
            return false;
        }

        return Hasil::where('nim', $nim)
            ->whereIn('id_pertanyaan', $pertanyaanIds)
            ->where('kode_makul', $makul->kode_makul)
            ->where('nipy', $makul->nipy)
            ->exists();
    }

    private function parseTahunAjaranInt(string $tahunAjaran): int
    {
        $trimmed = trim($tahunAjaran);
        if (ctype_digit($trimmed)) {
            return (int) $trimmed;
        }
        if (preg_match('/\d{4}/', $trimmed, $matches)) {
            return (int) $matches[0];
        }

        return (int) date('Y');
    }
}
