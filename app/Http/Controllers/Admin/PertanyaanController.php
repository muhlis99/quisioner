<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pertanyaan;
use App\Models\Quisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PertanyaanController extends Controller
{
    public function index(Quisioner $quisioner): View
    {
        abort_if($quisioner->hapus === '1', 404);

        $pertanyaans = $quisioner->pertanyaan()
            ->orderBy('kategori')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.pertanyaan.index', [
            'quisioner' => $quisioner,
            'pertanyaans' => $pertanyaans,
        ]);
    }

    public function create(Quisioner $quisioner): View
    {
        abort_if($quisioner->hapus === '1', 404);

        $kategoriList = $quisioner->pertanyaan()
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.pertanyaan.create', [
            'quisioner' => $quisioner,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function store(Request $request, Quisioner $quisioner): RedirectResponse
    {
        abort_if($quisioner->hapus === '1', 404);

        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:255'],
            'isi_pertanyaan' => ['required', 'string'],
            'type' => ['required', Rule::in(['ganda', 'esai'])],
        ], [
            'kategori.required' => 'Kategori pertanyaan wajib diisi.',
            'isi_pertanyaan.required' => 'Isi pertanyaan wajib diisi.',
            'type.required' => 'Tipe pertanyaan wajib dipilih.',
            'type.in' => 'Tipe pertanyaan harus Pilihan Ganda atau Isian/Esai.',
        ]);

        $quisioner->pertanyaan()->create($validated);

        return redirect()
            ->route('admin.quisioner.pertanyaan.index', $quisioner)
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function edit(Quisioner $quisioner, Pertanyaan $pertanyaan): View
    {
        abort_if($quisioner->hapus === '1' || (int) $pertanyaan->id_quisioner !== (int) $quisioner->id, 404);

        $kategoriList = $quisioner->pertanyaan()
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.pertanyaan.edit', [
            'quisioner' => $quisioner,
            'pertanyaan' => $pertanyaan,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function update(Request $request, Quisioner $quisioner, Pertanyaan $pertanyaan): RedirectResponse
    {
        abort_if($quisioner->hapus === '1' || (int) $pertanyaan->id_quisioner !== (int) $quisioner->id, 404);

        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:255'],
            'isi_pertanyaan' => ['required', 'string'],
            'type' => ['required', Rule::in(['ganda', 'esai'])],
        ], [
            'kategori.required' => 'Kategori pertanyaan wajib diisi.',
            'isi_pertanyaan.required' => 'Isi pertanyaan wajib diisi.',
            'type.required' => 'Tipe pertanyaan wajib dipilih.',
            'type.in' => 'Tipe pertanyaan harus Pilihan Ganda atau Isian/Esai.',
        ]);

        $pertanyaan->update($validated);

        return redirect()
            ->route('admin.quisioner.pertanyaan.index', $quisioner)
            ->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(Quisioner $quisioner, Pertanyaan $pertanyaan): RedirectResponse
    {
        abort_if((int) $pertanyaan->id_quisioner !== (int) $quisioner->id, 404);

        $pertanyaan->delete();

        return redirect()
            ->route('admin.quisioner.pertanyaan.index', $quisioner)
            ->with('success', 'Pertanyaan berhasil dihapus.');
    }
}
