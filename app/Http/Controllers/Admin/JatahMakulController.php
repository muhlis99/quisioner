<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\JatahMakul;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JatahMakulController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $semester = (string) $request->query('semester', '');
        $tahunAjaran = trim((string) $request->query('tahun_ajaran', ''));

        $tahunAjaranList = JatahMakul::query()
            ->select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran');

        $jatahMakul = JatahMakul::query()
            ->with(['mahasiswa', 'dosen'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('nim', 'like', "%{$search}%")
                        ->orWhere('kode_makul', 'like', "%{$search}%")
                        ->orWhere('nama_makul', 'like', "%{$search}%")
                        ->orWhere('nipy', 'like', "%{$search}%")
                        ->orWhere('nama_dosen', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($semester, ['ganjil', 'genap'], true), fn ($q) => $q->where('semester', $semester))
            ->when($tahunAjaran !== '', fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jatah-makul.index', [
            'jatahMakul' => $jatahMakul,
            'search' => $search,
            'semester' => $semester,
            'tahunAjaran' => $tahunAjaran,
            'tahunAjaranList' => $tahunAjaranList,
        ]);
    }

    public function create(): View
    {
        return view('admin.jatah-makul.create', [
            'mahasiswas' => Mahasiswa::orderBy('nim')->get(),
            'dosens' => Dosen::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:100'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'kode_makul' => ['required', 'string', 'max:100'],
            'nama_makul' => ['required', 'string', 'max:255'],
            'nipy' => ['required', 'string', 'max:100'],
            'nama_dosen' => ['nullable', 'string', 'max:255'],
        ], [
            'nim.required' => 'NIM mahasiswa wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'kode_makul.required' => 'Kode mata kuliah wajib diisi.',
            'nama_makul.required' => 'Nama mata kuliah wajib diisi.',
            'nipy.required' => 'NIPY dosen wajib diisi.',
        ]);

        if (empty(trim((string) ($validated['nama_dosen'] ?? '')))) {
            $dosen = Dosen::where('nipy', trim($validated['nipy']))->first();
            $validated['nama_dosen'] = $dosen ? $dosen->nama : '-';
        }

        JatahMakul::create($validated);

        return redirect()
            ->route('admin.jatah-makul.index')
            ->with('success', 'Data jatah mata kuliah berhasil ditambahkan.');
    }

    public function edit(JatahMakul $jatahMakul): View
    {
        return view('admin.jatah-makul.edit', [
            'jatahMakul' => $jatahMakul,
            'mahasiswas' => Mahasiswa::orderBy('nim')->get(),
            'dosens' => Dosen::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, JatahMakul $jatahMakul): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:100'],
            'tahun_ajaran' => ['required', 'string', 'max:50'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'kode_makul' => ['required', 'string', 'max:100'],
            'nama_makul' => ['required', 'string', 'max:255'],
            'nipy' => ['required', 'string', 'max:100'],
            'nama_dosen' => ['nullable', 'string', 'max:255'],
        ], [
            'nim.required' => 'NIM mahasiswa wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'semester.required' => 'Semester wajib dipilih.',
            'kode_makul.required' => 'Kode mata kuliah wajib diisi.',
            'nama_makul.required' => 'Nama mata kuliah wajib diisi.',
            'nipy.required' => 'NIPY dosen wajib diisi.',
        ]);

        if (empty(trim((string) ($validated['nama_dosen'] ?? '')))) {
            $dosen = Dosen::where('nipy', trim($validated['nipy']))->first();
            $validated['nama_dosen'] = $dosen ? $dosen->nama : '-';
        }

        $jatahMakul->update($validated);

        return redirect()
            ->route('admin.jatah-makul.index')
            ->with('success', 'Data jatah mata kuliah berhasil diperbarui.');
    }

    public function destroy(JatahMakul $jatahMakul): RedirectResponse
    {
        $jatahMakul->delete();

        return redirect()
            ->route('admin.jatah-makul.index')
            ->with('success', 'Data jatah mata kuliah berhasil dihapus.');
    }
}
