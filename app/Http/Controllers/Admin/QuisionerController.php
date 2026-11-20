<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuisionerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $semester = (string) $request->query('semester', '');
        $type = (string) $request->query('type', '');

        $quisioners = Quisioner::aktif()
            ->withCount('pertanyaan')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('judul_quisioner', 'like', "%{$search}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$search}%");
                });
            })
            ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
            ->when(in_array($type, ['edom', 'biasa'], true), fn ($q) => $q->where('type', $type))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.quisioner.index', [
            'quisioners' => $quisioners,
            'search' => $search,
            'semester' => $semester,
            'type' => $type,
        ]);
    }

    public function create(): View
    {
        return view('admin.quisioner.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul_quisioner' => ['required', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'type' => ['required', Rule::in(['edom', 'biasa'])],
        ], [
            'judul_quisioner.required' => 'Judul quisioner wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'type.required' => 'Tipe quisioner wajib dipilih.',
        ]);

        $validated['hapus'] = '0';

        Quisioner::create($validated);

        return redirect()
            ->route('admin.quisioner.index')
            ->with('success', 'Quisioner baru berhasil ditambahkan.');
    }

    public function edit(Quisioner $quisioner): View
    {
        abort_if($quisioner->hapus === '1', 404);

        return view('admin.quisioner.edit', compact('quisioner'));
    }

    public function update(Request $request, Quisioner $quisioner): RedirectResponse
    {
        abort_if($quisioner->hapus === '1', 404);

        $validated = $request->validate([
            'judul_quisioner' => ['required', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'type' => ['required', Rule::in(['edom', 'biasa'])],
        ], [
            'judul_quisioner.required' => 'Judul quisioner wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'type.required' => 'Tipe quisioner wajib dipilih.',
        ]);

        $quisioner->update($validated);

        return redirect()
            ->route('admin.quisioner.index')
            ->with('success', 'Data quisioner berhasil diperbarui.');
    }

    /**
     * Soft delete manual: mengubah kolom hapus (ENUM '0'/'1') menjadi '1'.
     */
    public function destroy(Quisioner $quisioner): RedirectResponse
    {
        $quisioner->update(['hapus' => '1']);

        return redirect()
            ->route('admin.quisioner.index')
            ->with('success', 'Quisioner berhasil dihapus (nonaktif).');
    }
}
