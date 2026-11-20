<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkorController extends Controller
{
    public function index(): View
    {
        $skors = Skor::query()
            ->orderBy('skor')
            ->paginate(15);

        return view('admin.skor.index', compact('skors'));
    }

    public function create(): View
    {
        return view('admin.skor.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'skor' => ['required', 'integer', 'min:0', 'max:100'],
            'keterangan' => ['required', 'string', 'max:255'],
            'huruf' => ['required', 'string', 'max:100'],
        ], [
            'skor.required' => 'Nilai skor wajib diisi.',
            'skor.integer' => 'Nilai skor harus berupa angka bulat.',
            'keterangan.required' => 'Keterangan skor wajib diisi.',
            'huruf.required' => 'Predikat huruf wajib diisi.',
        ]);

        Skor::create($validated);

        return redirect()
            ->route('admin.skor.index')
            ->with('success', 'Data skor jawaban berhasil ditambahkan.');
    }

    public function edit(Skor $skor): View
    {
        return view('admin.skor.edit', compact('skor'));
    }

    public function update(Request $request, Skor $skor): RedirectResponse
    {
        $validated = $request->validate([
            'skor' => ['required', 'integer', 'min:0', 'max:100'],
            'keterangan' => ['required', 'string', 'max:255'],
            'huruf' => ['required', 'string', 'max:100'],
        ], [
            'skor.required' => 'Nilai skor wajib diisi.',
            'skor.integer' => 'Nilai skor harus berupa angka bulat.',
            'keterangan.required' => 'Keterangan skor wajib diisi.',
            'huruf.required' => 'Predikat huruf wajib diisi.',
        ]);

        $skor->update($validated);

        return redirect()
            ->route('admin.skor.index')
            ->with('success', 'Data skor jawaban berhasil diperbarui.');
    }

    public function destroy(Skor $skor): RedirectResponse
    {
        $skor->delete();

        return redirect()
            ->route('admin.skor.index')
            ->with('success', 'Data skor jawaban berhasil dihapus.');
    }
}
