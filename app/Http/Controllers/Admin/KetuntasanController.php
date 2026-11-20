<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ketuntasan;
use App\Models\Quisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KetuntasanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $quisionerId = (string) $request->query('id_quisioner', '');
        $semester = (string) $request->query('semester', '');
        $status = (string) $request->query('ketuntasan', '');

        $baseQuery = Ketuntasan::query()
            ->with(['mahasiswa', 'quisioner'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('niim', 'like', "%{$search}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($quisionerId !== '' && ctype_digit($quisionerId), fn ($q) => $q->where('id_quisioner', (int) $quisionerId))
            ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
            ->when(in_array($status, ['0', '1'], true), fn ($q) => $q->where('ketuntasan', $status));

        $totalData = (clone $baseQuery)->count();
        $totalTuntas = (clone $baseQuery)->where('ketuntasan', '1')->count();
        $totalBelum = max(0, $totalData - $totalTuntas);
        $persentase = $totalData > 0 ? round(($totalTuntas / $totalData) * 100, 1) : 0.0;

        $ketuntasans = $baseQuery
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $quisionerList = Quisioner::aktif()
            ->orderByDesc('id')
            ->get(['id', 'judul_quisioner', 'tahun_ajaran', 'semester']);

        return view('admin.ketuntasan.index', [
            'ketuntasans' => $ketuntasans,
            'quisionerList' => $quisionerList,
            'search' => $search,
            'quisionerId' => $quisionerId,
            'semester' => $semester,
            'status' => $status,
            'totalData' => $totalData,
            'totalTuntas' => $totalTuntas,
            'totalBelum' => $totalBelum,
            'persentase' => $persentase,
        ]);
    }

    public function destroy(Ketuntasan $ketuntasan): RedirectResponse
    {
        $ketuntasan->delete();

        return redirect()
            ->route('admin.ketuntasan.index')
            ->with('success', 'Data ketuntasan berhasil dihapus.');
    }
}
