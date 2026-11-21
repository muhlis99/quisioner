@extends('layouts.admin')

@section('title', 'Tambah Jatah Mata Kuliah - Admin')
@section('page-title', 'Tambah Jatah Mata Kuliah')
@section('page-subtitle', 'Hubungkan mahasiswa dengan mata kuliah dan dosen pengampu untuk pengisian EDOM')

@section('content')
    <div class="mx-auto max-w-3xl overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-[#062419]">Formulir Jatah Mata Kuliah Baru</h2>
            <p class="mt-0.5 text-xs text-slate-500">Pilih mahasiswa, periode akademik, mata kuliah, dan dosen pengampu.</p>
        </div>

        <form method="POST" action="{{ route('admin.jatah-makul.store') }}" class="space-y-5 p-6 sm:p-8">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="nim" class="block text-sm font-semibold text-slate-700">NIM Mahasiswa <span class="text-rose-500">*</span></label>
                    <input
                        id="nim"
                        name="nim"
                        type="text"
                        list="list-mahasiswa"
                        value="{{ old('nim') }}"
                        required
                        placeholder="Pilih atau ketik NIM mahasiswa"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                    <datalist id="list-mahasiswa">
                        @foreach ($mahasiswas as $m)
                            <option value="{{ $m->nim }}">{{ $m->nim }} - {{ $m->nama }}</option>
                        @endforeach
                    </datalist>
                    @error('nim')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tahun_ajaran" class="block text-sm font-semibold text-slate-700">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input
                            id="tahun_ajaran"
                            name="tahun_ajaran"
                            type="text"
                            value="{{ old('tahun_ajaran', date('Y') . '/' . (date('Y') + 1)) }}"
                            required
                            placeholder="2025/2026"
                            class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                        >
                        @error('tahun_ajaran')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="semester" class="block text-sm font-semibold text-slate-700">Semester <span class="text-rose-500">*</span></label>
                        <select
                            id="semester"
                            name="semester"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                        >
                            <option value="">-- Pilih --</option>
                            <option value="ganjil" @selected(old('semester') === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                        </select>
                        @error('semester')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="kode_makul" class="block text-sm font-semibold text-slate-700">Kode Mata Kuliah <span class="text-rose-500">*</span></label>
                    <input
                        id="kode_makul"
                        name="kode_makul"
                        type="text"
                        value="{{ old('kode_makul') }}"
                        required
                        placeholder="Contoh: TI101"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                    @error('kode_makul')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_makul" class="block text-sm font-semibold text-slate-700">Nama Mata Kuliah <span class="text-rose-500">*</span></label>
                    <input
                        id="nama_makul"
                        name="nama_makul"
                        type="text"
                        value="{{ old('nama_makul') }}"
                        required
                        placeholder="Contoh: Algoritma & Pemrograman"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                    @error('nama_makul')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="nipy" class="block text-sm font-semibold text-slate-700">NIPY Dosen <span class="text-rose-500">*</span></label>
                    <input
                        id="nipy"
                        name="nipy"
                        type="text"
                        list="list-dosen"
                        value="{{ old('nipy') }}"
                        required
                        placeholder="Pilih atau ketik NIPY dosen"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                    <datalist id="list-dosen">
                        @foreach ($dosens as $d)
                            <option value="{{ $d->nipy }}">{{ $d->nipy }} - {{ $d->nama }}</option>
                        @endforeach
                    </datalist>
                    @error('nipy')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_dosen" class="block text-sm font-semibold text-slate-700">Nama Dosen</label>
                    <input
                        id="nama_dosen"
                        name="nama_dosen"
                        type="text"
                        value="{{ old('nama_dosen') }}"
                        placeholder="Otomatis terisi jika NIPY cocok"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                    @error('nama_dosen')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-emerald-100 pt-5">
                <a href="{{ route('admin.jatah-makul.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    &larr; Kembali
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                    <span>Simpan Jatah Makul</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dosenMap = @json($dosens->pluck('nama', 'nipy'));
            const nipyInput = document.getElementById('nipy');
            const namaDosenInput = document.getElementById('nama_dosen');

            nipyInput?.addEventListener('change', () => {
                const val = nipyInput.value.trim();
                if (dosenMap[val] && namaDosenInput) {
                    namaDosenInput.value = dosenMap[val];
                }
            });
        });
    </script>
@endpush
