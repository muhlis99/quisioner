<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Hasil;
use App\Models\JatahMakul;
use App\Models\Ketuntasan;
use App\Models\Pertanyaan;
use App\Models\Quisioner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KetuntasanController extends Controller
{
    public function index(Request $request): View
    {
        $nim = (string) ($request->session()->get('mahasiswa_nim') ?? $request->user()->username);
        $semester = (string) $request->query('semester', '');
        $status = (string) $request->query('ketuntasan', '');

        $quisionerAktif = Quisioner::aktif()
            ->withCount('pertanyaan')
            ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
            ->orderByDesc('id')
            ->get();

        $ketuntasanMap = Ketuntasan::where('niim', $nim)
            ->get()
            ->keyBy('id_quisioner');

        $allMakul = JatahMakul::where('nim', $nim)->get();
        $qIds = $quisionerAktif->pluck('id')->all();
        $pertanyaanByQ = Pertanyaan::whereIn('id_quisioner', empty($qIds) ? [-1] : $qIds)
            ->get(['id', 'id_quisioner'])
            ->groupBy('id_quisioner')
            ->map(fn ($rows) => $rows->pluck('id')->all());

        $allPIds = $pertanyaanByQ->flatten()->all();
        $hasilRows = empty($allPIds)
            ? collect()
            : Hasil::where('nim', $nim)
                ->whereIn('id_pertanyaan', $allPIds)
                ->get(['id_pertanyaan', 'kode_makul', 'nipy']);

        $statusAktif = $quisionerAktif->map(function (Quisioner $q) use ($ketuntasanMap, $allMakul, $pertanyaanByQ, $hasilRows) {
            $record = $ketuntasanMap->get($q->id);
            $target = $q->pertanyaan_count;
            $selesai = $record ? (int) $record->status_penyelesaian : 0;

            if ($q->type === 'edom') {
                $makulPeriode = $allMakul->filter(
                    fn (JatahMakul $m) => trim((string) $m->tahun_ajaran) === trim((string) $q->tahun_ajaran)
                        && strtolower((string) $m->semester) === strtolower((string) $q->semester)
                );

                if ($makulPeriode->isEmpty()) {
                    $makulPeriode = $allMakul->filter(
                        fn (JatahMakul $m) => strtolower((string) $m->semester) === strtolower((string) $q->semester)
                    );
                }

                $target = $makulPeriode->count();
                $pIds = $pertanyaanByQ->get($q->id, []);
                $qHasil = $hasilRows->whereIn('id_pertanyaan', $pIds);

                $selesai = $makulPeriode->filter(function (JatahMakul $m) use ($qHasil) {
                    return $qHasil->contains(
                        fn ($h) => trim((string) $h->kode_makul) === trim((string) $m->kode_makul)
                            && trim((string) $h->nipy) === trim((string) $m->nipy)
                    );
                })->count();
            } elseif ($record) {
                $target = max((int) $record->status_insert_soal, $q->pertanyaan_count);
            }

            $isTuntas = ($record && $record->ketuntasan === '1')
                || ($q->type === 'edom' && $target > 0 && $selesai >= $target);

            $persen = $target > 0 ? min(100, (int) round(($selesai / $target) * 100)) : ($isTuntas ? 100 : 0);

            return [
                'quisioner' => $q,
                'record' => $record,
                'target' => $target,
                'selesai' => $selesai,
                'persen' => $persen,
                'is_tuntas' => $isTuntas,
                'satuan' => $q->type === 'edom' ? 'mata kuliah' : 'pertanyaan',
            ];
        });

        if (in_array($status, ['0', '1'], true)) {
            $statusAktif = $statusAktif->filter(
                fn ($row) => $status === '1' ? $row['is_tuntas'] : ! $row['is_tuntas']
            )->values();
        }

        $totalAktif = $statusAktif->count();
        $totalTuntas = $statusAktif->where('is_tuntas', true)->count();
        $totalBelum = max(0, $totalAktif - $totalTuntas);
        $persentase = $totalAktif > 0 ? round(($totalTuntas / $totalAktif) * 100, 1) : 0.0;

        $riwayatKetuntasan = Ketuntasan::with('quisioner')
            ->where('niim', $nim)
            ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
            ->when(in_array($status, ['0', '1'], true), fn ($q) => $q->where('ketuntasan', $status))
            ->orderByDesc('id')
            ->get();

        return view('mahasiswa.ketuntasan.index', [
            'statusAktif' => $statusAktif,
            'riwayatKetuntasan' => $riwayatKetuntasan,
            'semester' => $semester,
            'status' => $status,
            'totalAktif' => $totalAktif,
            'totalTuntas' => $totalTuntas,
            'totalBelum' => $totalBelum,
            'persentase' => $persentase,
        ]);
    }
}
