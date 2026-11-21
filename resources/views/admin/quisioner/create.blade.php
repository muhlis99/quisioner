@extends('layouts.admin')

@section('title', 'Tambah Quisioner - Admin')
@section('page-title', 'Tambah Quisioner')
@section('page-subtitle', 'Buat instrumen kuisioner EDOM atau kuisioner biasa baru')

@section('content')
    <div class="mx-auto max-w-2xl overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-[#062419]">Formulir Quisioner Baru</h2>
            <p class="mt-0.5 text-xs text-slate-500">Tentukan judul, tahun ajaran, semester, dan tipe quisioner.</p>
        </div>

        <form method="POST" action="{{ route('admin.quisioner.store') }}" class="space-y-5 p-6 sm:p-8">
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
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                @error('judul_quisioner')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
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
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                @error('tahun_ajaran')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="semester" class="block text-sm font-semibold text-slate-700">Semester <span class="text-rose-500">*</span></label>
                    <select
                        id="semester"
                        name="semester"
                        required
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                        <option value="">-- Pilih Semester --</option>
                        <option value="ganjil" @selected(old('semester') === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                    </select>
                    @error('semester')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-700">Tipe Quisioner <span class="text-rose-500">*</span></label>
                    <select
                        id="type"
                        name="type"
                        required
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                        <option value="">-- Pilih Tipe --</option>
                        <option value="edom" @selected(old('type') === 'edom')>EDOM (Evaluasi Dosen)</option>
                        <option value="biasa" @selected(old('type') === 'biasa')>Biasa (Kuisioner Umum)</option>
                    </select>
                    @error('type')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-emerald-100 pt-5">
                <a href="{{ route('admin.quisioner.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    &larr; Kembali
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                    <span>Simpan Quisioner</span>
                </button>
            </div>
        </form>
    </div>
@endsection
