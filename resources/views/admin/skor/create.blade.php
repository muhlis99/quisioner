@extends('layouts.admin')

@section('title', 'Tambah Skor Jawaban - Admin')
@section('page-title', 'Tambah Skor Jawaban')
@section('page-subtitle', 'Tambahkan opsi nilai skor untuk penilaian kuisioner')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <form method="POST" action="{{ route('admin.skor.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="skor" class="block text-sm font-semibold text-slate-700">Nilai Skor <span class="text-rose-500">*</span></label>
                <input
                    id="skor"
                    name="skor"
                    type="number"
                    min="0"
                    max="100"
                    value="{{ old('skor') }}"
                    required
                    placeholder="Contoh: 4"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @error('skor')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-semibold text-slate-700">Keterangan <span class="text-rose-500">*</span></label>
                <input
                    id="keterangan"
                    name="keterangan"
                    type="text"
                    value="{{ old('keterangan') }}"
                    required
                    placeholder="Contoh: Sangat Baik / Sangat Setuju"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @error('keterangan')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="huruf" class="block text-sm font-semibold text-slate-700">Huruf / Predikat <span class="text-rose-500">*</span></label>
                <input
                    id="huruf"
                    name="huruf"
                    type="text"
                    value="{{ old('huruf') }}"
                    required
                    placeholder="Contoh: A / SB"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @error('huruf')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.skor.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                    Simpan Skor
                </button>
            </div>
        </form>
    </div>
@endsection
