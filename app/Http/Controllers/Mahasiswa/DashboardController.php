<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Hasil;
use App\Models\JatahMakul;
use App\Models\Ketuntasan;
use App\Models\Quisioner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $nim = (string) ($request->session()->get('mahasiswa_nim') ?? $request->user()->username);

        $quisioners = Quisioner::aktif()
            ->withCount('pertanyaan')
            ->orderByDesc('id')
            ->get();

        $ketuntasanMap = Ketuntasan::where('niim', $nim)
            ->get()
            ->keyBy('id_quisioner');

        $jumlahMakul = JatahMakul::where('nim', $nim)->count();
        $totalAktif = $quisioners->count();
        $sudahTuntas = 0;

        foreach ($quisioners as $q) {
            $k = $ketuntasanMap->get($q->id);
            if ($k && $k->ketuntasan === '1') {
                $sudahTuntas++;
            }
        }

        $belumDiisi = max(0, $totalAktif - $sudahTuntas);

        if ($totalAktif === 0) {
            $statusKetuntasan = 'Belum Ada Kuisioner';
        } elseif ($belumDiisi === 0) {
            $statusKetuntasan = 'Tuntas';
        } else {
            $statusKetuntasan = "{$sudahTuntas} / {$totalAktif} Tuntas";
        }

        $totalJawaban = Hasil::where('nim', $nim)->count();

        return view('mahasiswa.dashboard', [
            'jumlahMakul' => $jumlahMakul,
            'belumDiisi' => $belumDiisi,
            'statusKetuntasan' => $statusKetuntasan,
            'totalAktif' => $totalAktif,
            'sudahTuntas' => $sudahTuntas,
            'totalJawaban' => $totalJawaban,
        ]);
    }
}
