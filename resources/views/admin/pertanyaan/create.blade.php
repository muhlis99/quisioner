@extends('layouts.admin')

@section('title', 'Tambah Pertanyaan - Admin')
@section('page-title', 'Tambah Pertanyaan')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <form method="POST" action="{{ route('admin.quisioner.pertanyaan.store', $quisioner) }}" class="space-y-5">
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
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                <datalist id="kategori-options">
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat }}"></option>
                    @endforeach
                </datalist>
                @error('kategori')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
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
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >{{ old('isi_pertanyaan') }}</textarea>
                @error('isi_pertanyaan')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-semibold text-slate-700">Tipe Pertanyaan <span class="text-rose-500">*</span></label>
                <select
                    id="type"
                    name="type"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                    <option value="">-- Pilih Tipe Pertanyaan --</option>
                    <option value="ganda" @selected(old('type') === 'ganda')>Pilihan Ganda (Skala Skor)</option>
                    <option value="esai" @selected(old('type') === 'esai')>Isian / Esai (Teks Bebas)</option>
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.quisioner.pertanyaan.index', $quisioner) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                    Simpan Pertanyaan
                </button>
            </div>
        </form>
    </div>
@endsection
