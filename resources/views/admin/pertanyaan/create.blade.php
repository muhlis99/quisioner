@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan - Admin')
@section('page-title', 'Tambah Pertanyaan')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('content')
    <div class="mx-auto max-w-2xl overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-[#062419]">Formulir Pertanyaan Baru</h2>
            <p class="mt-0.5 text-xs text-slate-500">Quisioner: <strong class="text-[#0B6B3A]">{{ $quisioner->judul_quisioner }}</strong></p>
        </div>

        <form method="POST" action="{{ route('admin.quisioner.pertanyaan.store', $quisioner) }}" class="space-y-5 p-6 sm:p-8">
            @csrf

            <div>
                <label for="kategori" class="block text-sm font-semibold text-slate-700">Kategori <span class="text-rose-500">*</span></label>
                <input
                    id="kategori"
                    name="kategori"
                    type="text"
                    list="kategori-options"
                    value="{{ old('kategori') }}"
                    required
                    placeholder="Contoh: Kompetensi Pedagogik / Kompetensi Profesional"
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                <datalist id="kategori-options">
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}"></option>
                    @endforeach
                </datalist>
                @error('kategori')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="isi_pertanyaan" class="block text-sm font-semibold text-slate-700">Isi Pertanyaan <span class="text-rose-500">*</span></label>
                <textarea
                    id="isi_pertanyaan"
                    name="isi_pertanyaan"
                    rows="4"
                    required
                    placeholder="Tuliskan butir pertanyaan kuisioner secara jelas..."
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >{{ old('isi_pertanyaan') }}</textarea>
                @error('isi_pertanyaan')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-semibold text-slate-700">Tipe Pertanyaan <span class="text-rose-500">*</span></label>
                <select
                    id="type"
                    name="type"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                    <option value="">-- Pilih Tipe Pertanyaan --</option>
                    <option value="ganda" @selected(old('type') === 'ganda')>Pilihan Ganda (Skala Skor)</option>
                    <option value="esai" @selected(old('type') === 'esai')>Isian / Esai (Teks Bebas)</option>
                </select>
                @error('type')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-emerald-100 pt-5">
                <a href="{{ route('admin.quisioner.pertanyaan.index', $quisioner) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    &larr; Kembali
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                    <span>Simpan Pertanyaan</span>
                </button>
            </div>
        </form>
    </div>
@endsection
