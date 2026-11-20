@extends('layouts.admin')

@section('title', 'Tambah Quisioner - Admin')
@section('page-title', 'Tambah Quisioner')
@section('page-subtitle', 'Buat instrumen kuisioner EDOM atau kuisioner biasa baru')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <form method="POST" action="{{ route('admin.quisioner.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="judul_quisioner" class="block text-sm font-semibold text-slate-700">Judul Quisioner <span class="text-rose-500">*</span></label>
                <input
                    id="judul_quisioner"
                    name="judul_quisioner"
                    type="text"
                    value="{{ old('judul_quisioner') }}"
                    required
                    placeholder="Contoh: Evaluasi Dosen Oleh Mahasiswa (EDOM) 2025/2026"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @error('judul_quisioner')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tahun_ajaran" class="block text-sm font-semibold text-slate-700">Tahun Ajaran <span class="text-rose-500">*</span></label>
                <input
                    id="tahun_ajaran"
                    name="tahun_ajaran"
                    type="text"
                    value="{{ old('tahun_ajaran', date('Y') . '/' . (date('Y') + 1)) }}"
                    required
                    placeholder="Contoh: 2025/2026"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @error('tahun_ajaran')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="semester" class="block text-sm font-semibold text-slate-700">Semester <span class="text-rose-500">*</span></label>
                    <select
                        id="semester"
                        name="semester"
                        required
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    >
                        <option value="">-- Pilih Semester --</option>
                        <option value="ganjil" @selected(old('semester') === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                    </select>
                    @error('semester')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-700">Tipe Quisioner <span class="text-rose-500">*</span></label>
                    <select
                        id="type"
                        name="type"
                        required
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    >
                        <option value="">-- Pilih Tipe --</option>
                        <option value="edom" @selected(old('type') === 'edom')>EDOM (Evaluasi Dosen)</option>
                        <option value="biasa" @selected(old('type') === 'biasa')>Biasa (Kuisioner Umum)</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.quisioner.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                    Simpan Quisioner
                </button>
            </div>
        </form>
    </div>
@endsection
