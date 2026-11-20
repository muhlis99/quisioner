<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Hasil;
use App\Models\JatahMakul;
use App\Models\Pertanyaan;
use App\Models\Quisioner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MakulController extends Controller
{
    public function index(Request $request): View
    {
        $nim = (string) ($request->session()->get('mahasiswa_nim') ?? $request->user()->username);
        $search = trim((string) $request->query('search', ''));
        $tahunAjaran = trim((string) $request->query('tahun_ajaran', ''));
        $semester = (string) $request->query('semester', '');

        $tahunAjaranList = JatahMakul::where('nim', $nim)
            ->select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran');

        $makuls = JatahMakul::where('nim', $nim)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('kode_makul', 'like', "%{$search}%")
                        ->orWhere('nama_makul', 'like', "%{$search}%")
                        ->orWhere('nama_dosen', 'like', "%{$search}%")
                        ->orWhere('nipy', 'like', "%{$search}%");
                });
            })
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
            ->orderByDesc('tahun_ajaran')
            ->orderBy('nama_makul')
            ->paginate(15)
            ->withQueryString();

        // Cocokkan dengan kuisioner EDOM aktif untuk menampilkan status pengisian EDOM per makul
        $edomQuisioners = Quisioner::aktif()
            ->where('type', 'edom')
            ->withCount('pertanyaan')
            ->orderByDesc('id')
            ->get();

        $edomIds = $edomQuisioners->pluck('id')->all();
        $pertanyaanByEdom = Pertanyaan::whereIn('id_quisioner', empty($edomIds) ? [-1] : $edomIds)
            ->get(['id', 'id_quisioner'])
            ->groupBy('id_quisioner')
            ->map(fn ($rows) => $rows->pluck('id')->all());

        $allPertanyaanIds = $pertanyaanByEdom->flatten()->all();
        $hasilRows = empty($allPertanyaanIds)
            ? collect()
            : Hasil::where('nim', $nim)
                ->whereIn('id_pertanyaan', $allPertanyaanIds)
                ->get(['id_pertanyaan', 'kode_makul', 'nipy']);

        $edomStatusByMakulId = [];
        foreach ($makuls as $m) {
            $matchedEdom = $edomQuisioners->first(function (Quisioner $q) use ($m) {
                return trim((string) $q->tahun_ajaran) === trim((string) $m->tahun_ajaran)
                    && strtolower((string) $q->semester) === strtolower((string) $m->semester);
            });

            if (! $matchedEdom) {
                $matchedEdom = $edomQuisioners->first(
                    fn (Quisioner $q) => strtolower((string) $q->semester) === strtolower((string) $m->semester)
                );
            }

            if (! $matchedEdom) {
                $edomStatusByMakulId[$m->id] = [
                    'quisioner' => null,
                    'sudah_diisi' => false,
                ];
                continue;
            }

            $pIds = $pertanyaanByEdom->get($matchedEdom->id, []);
            $sudahDiisi = $hasilRows
                ->whereIn('id_pertanyaan', $pIds)
                ->contains(fn ($h) => trim((string) $h->kode_makul) === trim((string) $m->kode_makul)
                    && trim((string) $h->nipy) === trim((string) $m->nipy));

            $edomStatusByMakulId[$m->id] = [
                'quisioner' => $matchedEdom,
                'sudah_diisi' => $sudahDiisi,
            ];
        }

        $totalMakulSemua = JatahMakul::where('nim', $nim)->count();

        return view('mahasiswa.makul.index', [
            'makuls' => $makuls,
            'edomStatusByMakulId' => $edomStatusByMakulId,
            'tahunAjaranList' => $tahunAjaranList,
            'search' => $search,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'totalMakulSemua' => $totalMakulSemua,
        ]);
    }
}
