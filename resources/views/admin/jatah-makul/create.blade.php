@extends('layouts.admin')

@section('title', 'Tambah Jatah Mata Kuliah - Admin')
@section('page-title', 'Tambah Jatah Mata Kuliah')
@section('page-subtitle', 'Generate otomatis jadwal kuliah dari API SIAMDOS atau tambahkan secara manual')

@section('header-actions')
    <a href="{{ route('admin.jatah-makul.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50">
        &larr; Kembali ke Daftar Jatah Makul
    </a>
@endsection

@section('content')
    @php
        $initialTab = $errors->any() || old('nim') ? 'manual' : 'api';
        $oldSemester = old('semester', 'ganjil');
        $oldNim = (string) old('nim', '');
        $oldKodeMakul = (string) old('kode_makul', '');
        $oldNamaMakul = (string) old('nama_makul', '');
        $oldNipy = (string) old('nipy', '');
        $oldNamaDosen = (string) old('nama_dosen', '');
        $mahasiswasPayload = $mahasiswas->map(fn ($m) => ['nim' => (string) $m->nim, 'nama' => (string) $m->nama])->values();
        $dosensPayload = $dosens->map(fn ($d) => ['nipy' => (string) $d->nipy, 'nama' => (string) $d->nama])->values();
    @endphp

    <div
        id="admin-jatah-makul-create"
        data-initial-tab="{{ $initialTab }}"
        data-preselected-nim="{{ $preselectedNim ?? '' }}"
        data-fetch-url="{{ route('admin.jatah-makul.fetch-api') }}"
        data-store-api-url="{{ route('admin.jatah-makul.store-api') }}"
        data-massal-url="{{ route('admin.jatah-makul.generate-massal') }}"
        data-index-url="{{ route('admin.jatah-makul.index') }}"
        data-total-students="{{ $mahasiswas->count() }}"
        data-mahasiswas='@json($mahasiswasPayload)'
        data-dosens='@json($dosensPayload)'
        data-makuls='@json($makulCatalog)'
        class="mx-auto max-w-4xl space-y-6"
    >
        {{-- Card Utama dengan Segmented Tab Switcher --}}
        <div class="overflow-visible rounded-2xl border border-emerald-100 bg-white shadow-sm">
            {{-- Header Card + Segmented Tab Switcher --}}
            <div class="rounded-t-2xl border-b border-emerald-100 bg-gradient-to-r from-[#062419] via-[#0B6B3A] to-[#137c47] p-5 text-white sm:px-7 sm:py-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-emerald-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            Pengaturan EDOM &amp; Mata Kuliah
                        </span>
                        <h2 class="mt-2 text-lg font-extrabold tracking-tight sm:text-xl">Tambah Jatah Mata Kuliah Mahasiswa</h2>
                        <p class="mt-1 text-xs text-emerald-100/85">
                            Tarik jadwal kuliah langsung dari API SIAMDOS atau pilih data secara manual tanpa mengetik kode.
                        </p>
                    </div>

                    {{-- Segmented Tab Switcher --}}
                    <div class="inline-grid w-full grid-cols-2 rounded-2xl bg-[#062419]/60 p-1.5 ring-1 ring-white/15 backdrop-blur-xs lg:w-auto lg:min-w-[340px]" role="tablist">
                        <button
                            type="button"
                            data-jm-tab="api"
                            aria-selected="{{ $initialTab === 'api' ? 'true' : 'false' }}"
                            class="jm-mode-tab inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-200 {{ $initialTab === 'api' ? 'bg-[#20A464] text-white shadow-md' : 'text-emerald-100/80 hover:text-white' }}"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            <span>Generate Otomatis (API)</span>
                        </button>
                        <button
                            type="button"
                            data-jm-tab="manual"
                            aria-selected="{{ $initialTab === 'manual' ? 'true' : 'false' }}"
                            class="jm-mode-tab inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-200 {{ $initialTab === 'manual' ? 'bg-[#20A464] text-white shadow-md' : 'text-emerald-100/80 hover:text-white' }}"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                            <span>Input Manual</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- MODE 1: GENERATE OTOMATIS DARI API SIAMDOS --}}
            {{-- ================================================================= --}}
            <div id="jm-panel-api" class="{{ $initialTab === 'api' ? '' : 'hidden' }} space-y-6 p-6 sm:p-7">
                {{-- Switch Sub-Mode: Per Mahasiswa vs Generate Massal --}}
                <div class="flex flex-col justify-between gap-3 rounded-2xl border border-emerald-100 bg-[#F4F7F5] p-4 sm:flex-row sm:items-center">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-[#0B6B3A]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold text-[#062419] sm:text-sm">Sinkronisasi Jadwal Kuliah SIAMDOS</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Pilih 1 mahasiswa untuk preview &amp; pilih mata kuliah, atau aktifkan <strong>Generate Massal</strong> untuk seluruh mahasiswa.
                            </p>
                        </div>
                    </div>

                    <label class="inline-flex shrink-0 cursor-pointer items-center gap-2.5 self-start rounded-xl border border-emerald-200 bg-white px-3.5 py-2 shadow-2xs sm:self-center">
                        <input id="jm-toggle-massal" type="checkbox" class="sr-only peer">
                        <span class="relative h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-[#20A464] after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow-xs after:transition-all peer-checked:after:translate-x-4"></span>
                        <span class="text-xs font-bold text-[#062419]">Generate Massal</span>
                    </label>
                </div>

                {{-- Sub-Mode 1A: Per Mahasiswa (Tarik & Preview Jadwal) --}}
                <div id="jm-api-single-section" class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                        {{-- Searchable Picker Mahasiswa (API Mode) --}}
                        <div class="lg:col-span-6">
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                <span>Pilih Mahasiswa <span class="text-rose-500">*</span></span>
                            </label>
                            <div class="jm-combobox relative" data-combo="api-mahasiswa">
                                <input type="hidden" id="jm-api-nim" value="">
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                                        </svg>
                                    </span>
                                    <input
                                        type="text"
                                        id="jm-api-nim-search"
                                        autocomplete="off"
                                        placeholder="Ketik NIM atau Nama Mahasiswa..."
                                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pr-9 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                                    >
                                    <button type="button" data-combo-clear class="hidden absolute inset-y-0 right-0 items-center pr-3 text-slate-400 hover:text-rose-500" aria-label="Hapus pilihan">&times;</button>
                                </div>
                                <div data-combo-list class="hidden absolute z-30 mt-1.5 max-h-60 w-full overflow-y-auto rounded-xl border border-emerald-100 bg-white p-1.5 shadow-xl"></div>
                                <div id="jm-api-nim-badge" class="mt-2 hidden items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3 py-2 text-xs"></div>
                            </div>
                        </div>

                        {{-- Tahun Ajaran (API Mode) --}}
                        <div class="lg:col-span-3">
                            <label for="jm-api-ta" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span>Tahun Ajaran <span class="text-rose-500">*</span></span>
                            </label>
                            <input
                                id="jm-api-ta"
                                type="text"
                                value="{{ $defaultTahunAjaran }}"
                                placeholder="2026/2027"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-800 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                            >
                        </div>

                        {{-- Segmented Pill Semester (API Mode) --}}
                        <div class="lg:col-span-3">
                            <span class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Semester <span class="text-rose-500">*</span></span>
                            </span>
                            <div class="grid grid-cols-2 gap-1.5 rounded-xl border border-slate-200 bg-[#F4F7F5] p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="api_semester" value="ganjil" checked class="sr-only peer">
                                    <span class="flex items-center justify-center rounded-lg py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-[#20A464] peer-checked:text-white peer-checked:shadow-xs">Ganjil</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="api_semester" value="genap" class="sr-only peer">
                                    <span class="flex items-center justify-center rounded-lg py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-[#20A464] peer-checked:text-white peer-checked:shadow-xs">Genap</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Tarik & Preview Jadwal --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">
                            Endpoint: <code class="rounded bg-emerald-50 px-1.5 py-0.5 font-mono text-[11px] text-[#0B6B3A]">/jadwalKuliah/getJadwalMahasiswa/{nim}</code>
                        </p>
                        <button
                            id="jm-btn-fetch-api"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#17804D] disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <svg id="jm-fetch-spinner" class="hidden h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg id="jm-fetch-icon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span id="jm-fetch-btn-text">Tarik &amp; Preview Jadwal dari API</span>
                        </button>
                    </div>

                    {{-- Hasil Preview API (Identitas + Tabel Checkbox Interaktif) --}}
                    <div id="jm-preview-container" class="hidden space-y-4 border-t border-emerald-100 pt-5">
                        {{-- Identitas SIAMDOS --}}
                        <div id="jm-identitas-box" class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-4"></div>

                        {{-- Tabel Preview Jadwal --}}
                        <div id="jm-preview-table-wrap" class="overflow-hidden rounded-xl border border-emerald-100 bg-white">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-emerald-100 bg-emerald-50/70 px-4 py-3">
                                <p class="text-xs font-bold text-[#062419]">
                                    Preview Jadwal Mata Kuliah (<span id="jm-preview-count">0</span> ditemukan)
                                </p>
                                <span id="jm-preview-selected-info" class="text-xs font-semibold text-[#0B6B3A]">0 dipilih</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-emerald-100 text-left text-xs sm:text-sm">
                                    <thead class="bg-emerald-50 text-[11px] font-bold uppercase tracking-wider text-emerald-900">
                                        <tr>
                                            <th class="w-12 px-4 py-3 text-center">
                                                <input id="jm-check-all" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-[#20A464] focus:ring-[#20A464]">
                                            </th>
                                            <th class="w-32 px-4 py-3">Kode Makul</th>
                                            <th class="px-4 py-3">Nama Mata Kuliah</th>
                                            <th class="w-32 px-4 py-3">NIPY</th>
                                            <th class="px-4 py-3">Nama Dosen</th>
                                            <th class="w-28 px-4 py-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jm-preview-tbody" class="divide-y divide-slate-100 bg-white"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Empty State jika API mengembalikan data: [] --}}
                        <div id="jm-preview-empty" class="hidden flex-col items-center justify-center rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 px-6 py-8 text-center">
                            <p class="text-sm font-bold text-amber-900">Belum Ada Jadwal Mata Kuliah di API SIAMDOS</p>
                            <p class="mt-1 max-w-md text-xs text-amber-800/85">
                                Server SIAMDOS merespons sukses, tetapi daftar jadwal (`data: []`) untuk NIM ini masih kosong pada periode berjalan. Anda tetap bisa menginput jadwal secara manual.
                            </p>
                            <button
                                id="jm-switch-manual-from-empty"
                                type="button"
                                class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-[#0B6B3A] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#20A464]"
                            >
                                <span>Beralih ke Input Manual untuk NIM Ini</span>
                                <span>&rarr;</span>
                            </button>
                        </div>

                        {{-- Tombol Simpan Jadwal Terpilih --}}
                        <div id="jm-preview-actions" class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <p class="text-xs text-slate-500">
                                Item berstatus <strong>Sudah Ada</strong> akan diperbarui tanpa membuat duplikat (`updateOrCreate`).
                            </p>
                            <button
                                id="jm-btn-store-api"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#0B6B3A] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#20A464] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span id="jm-store-api-text">Simpan Jadwal Terpilih</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Sub-Mode 1B (Bonus): Generate Massal Semua Mahasiswa --}}
                <div id="jm-api-massal-section" class="hidden space-y-5 rounded-2xl border border-emerald-200/80 bg-emerald-50/40 p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-[#062419]">Generate Massal Jadwal (Semua Mahasiswa)</h3>
                            <p class="mt-0.5 text-xs text-slate-600">
                                Menarik jadwal kuliah dari SIAMDOS secara bertahap untuk seluruh <strong class="text-[#0B6B3A]">{{ number_format($mahasiswas->count()) }} mahasiswa</strong> di `tb_mahasiswa` (anti-duplikat &amp; bebas timeout).
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jm-massal-ta" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Tahun Ajaran</label>
                            <input
                                id="jm-massal-ta"
                                type="text"
                                value="{{ $defaultTahunAjaran }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-800 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                            >
                        </div>
                        <div>
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">Semester</span>
                            <div class="grid grid-cols-2 gap-1.5 rounded-xl border border-slate-200 bg-white p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="massal_semester" value="ganjil" checked class="sr-only peer">
                                    <span class="flex items-center justify-center rounded-lg py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-[#20A464] peer-checked:text-white">Ganjil</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="massal_semester" value="genap" class="sr-only peer">
                                    <span class="flex items-center justify-center rounded-lg py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-[#20A464] peer-checked:text-white">Genap</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar Generate Massal --}}
                    <div id="jm-massal-progress-wrap" class="hidden space-y-2 rounded-xl border border-emerald-200 bg-white p-4">
                        <div class="flex items-center justify-between text-xs font-semibold text-[#062419]">
                            <span id="jm-massal-status">Memproses...</span>
                            <span id="jm-massal-percent" class="font-mono text-[#0B6B3A] tabular-nums">0%</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-emerald-100">
                            <div id="jm-massal-bar" class="h-full rounded-full bg-[#20A464] transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="jm-massal-counters" class="text-[11px] text-slate-500 tabular-nums"></p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            id="jm-btn-massal-start"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#17804D] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span>Mulai Generate Massal</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- MODE 2: INPUT MANUAL DENGAN SEARCHABLE COMBOBOX --}}
            {{-- ================================================================= --}}
            <div id="jm-panel-manual" class="{{ $initialTab === 'manual' ? '' : 'hidden' }}">
                <form method="POST" action="{{ route('admin.jatah-makul.store') }}" class="space-y-6 p-6 sm:p-7">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                        {{-- 1. Pilih Mahasiswa (Searchable Combobox) --}}
                        <div class="lg:col-span-6">
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347" />
                                </svg>
                                <span>Pilih Mahasiswa <span class="text-rose-500">*</span></span>
                            </label>
                            <div class="jm-combobox relative" data-combo="manual-mahasiswa">
                                <input type="hidden" id="jm-manual-nim" name="nim" value="{{ $oldNim }}">
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                                        </svg>
                                    </span>
                                    <input
                                        type="text"
                                        id="jm-manual-nim-search"
                                        value="{{ $oldNim }}"
                                        autocomplete="off"
                                        placeholder="Cari NIM atau Nama Mahasiswa..."
                                        class="w-full rounded-xl border @error('nim') border-rose-400 @else border-slate-200 @enderror bg-white py-2.5 pr-9 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                                    >
                                    <button type="button" data-combo-clear class="hidden absolute inset-y-0 right-0 items-center pr-3 text-slate-400 hover:text-rose-500" aria-label="Hapus pilihan">&times;</button>
                                </div>
                                <div data-combo-list class="hidden absolute z-30 mt-1.5 max-h-60 w-full overflow-y-auto rounded-xl border border-emerald-100 bg-white p-1.5 shadow-xl"></div>
                                <div id="jm-manual-nim-badge" class="mt-2 hidden items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3 py-2 text-xs"></div>
                            </div>
                            @error('nim')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 2. Tahun Ajaran --}}
                        <div class="lg:col-span-3">
                            <label for="tahun_ajaran" class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25" />
                                </svg>
                                <span>Tahun Ajaran <span class="text-rose-500">*</span></span>
                            </label>
                            <input
                                id="tahun_ajaran"
                                name="tahun_ajaran"
                                type="text"
                                value="{{ old('tahun_ajaran', $defaultTahunAjaran) }}"
                                required
                                placeholder="2026/2027"
                                class="block w-full rounded-xl border @error('tahun_ajaran') border-rose-400 @else border-slate-200 @enderror bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                            >
                            @error('tahun_ajaran')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 3. Segmented Pill Toggle Semester (Ganjil / Genap) --}}
                        <div class="lg:col-span-3">
                            <span class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Semester <span class="text-rose-500">*</span></span>
                            </span>
                            <div class="grid grid-cols-2 gap-1.5 rounded-xl border border-slate-200 bg-[#F4F7F5] p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="semester" value="ganjil" @checked($oldSemester === 'ganjil') required class="sr-only peer">
                                    <span class="flex items-center justify-center rounded-lg py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-[#20A464] peer-checked:text-white peer-checked:shadow-xs">Ganjil</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="semester" value="genap" @checked($oldSemester === 'genap') required class="sr-only peer">
                                    <span class="flex items-center justify-center rounded-lg py-2 text-xs font-bold text-slate-600 transition peer-checked:bg-[#20A464] peer-checked:text-white peer-checked:shadow-xs">Genap</span>
                                </label>
                            </div>
                            @error('semester')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 4. Pilih Mata Kuliah (Searchable Combobox + Tombol "+ Input Makul Baru") --}}
                    <div class="rounded-2xl border border-emerald-100 bg-[#F4F7F5]/65 p-5">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                                <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                                <span>Mata Kuliah <span class="text-rose-500">*</span></span>
                            </label>
                            <button
                                id="jm-btn-toggle-new-makul"
                                type="button"
                                class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-white px-3 py-1 text-[11px] font-bold text-[#0B6B3A] shadow-2xs transition hover:bg-[#20A464] hover:text-white"
                            >
                                <span>+ Input Makul Baru</span>
                            </button>
                        </div>

                        {{-- Picker Katalog Mata Kuliah --}}
                        <div id="jm-makul-picker-wrap" class="jm-combobox relative" data-combo="manual-makul">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    id="jm-manual-makul-search"
                                    autocomplete="off"
                                    placeholder="Cari kode atau nama mata kuliah dari daftar tersimpan..."
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pr-9 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                                >
                                <button type="button" data-combo-clear class="hidden absolute inset-y-0 right-0 items-center pr-3 text-slate-400 hover:text-rose-500" aria-label="Hapus pilihan">&times;</button>
                            </div>
                            <div data-combo-list class="hidden absolute z-30 mt-1.5 max-h-60 w-full overflow-y-auto rounded-xl border border-emerald-100 bg-white p-1.5 shadow-xl"></div>
                            <div id="jm-manual-makul-badge" class="mt-2.5 hidden items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 text-xs shadow-2xs"></div>
                        </div>

                        {{-- Form Input Makul Baru (Tampil saat klik "+ Input Makul Baru" atau jika katalog kosong / error validasi) --}}
                        @php $showNewMakulInputs = $makulCatalog->isEmpty() || $oldKodeMakul !== '' || $errors->has('kode_makul') || $errors->has('nama_makul'); @endphp
                        <div id="jm-makul-new-inputs" class="{{ $showNewMakulInputs ? '' : 'hidden' }} mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="kode_makul" class="mb-1 block text-xs font-semibold text-slate-700">Kode Mata Kuliah <span class="text-rose-500">*</span></label>
                                <input
                                    id="kode_makul"
                                    name="kode_makul"
                                    type="text"
                                    value="{{ $oldKodeMakul }}"
                                    required
                                    placeholder="Contoh: PAI201"
                                    class="block w-full rounded-xl border @error('kode_makul') border-rose-400 @else border-slate-200 @enderror bg-white px-3.5 py-2.5 font-mono text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                                >
                                @error('kode_makul')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="nama_makul" class="mb-1 block text-xs font-semibold text-slate-700">Nama Mata Kuliah <span class="text-rose-500">*</span></label>
                                <input
                                    id="nama_makul"
                                    name="nama_makul"
                                    type="text"
                                    value="{{ $oldNamaMakul }}"
                                    required
                                    placeholder="Contoh: Metodologi Pendidikan Islam"
                                    class="block w-full rounded-xl border @error('nama_makul') border-rose-400 @else border-slate-200 @enderror bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                                >
                                @error('nama_makul')
                                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 5. Pilih Dosen Pengampu (Searchable Combobox -> Auto-isi NIPY & Nama Dosen) --}}
                    <div>
                        <label class="mb-1.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-600">
                            <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span>Dosen Pengampu <span class="text-rose-500">*</span></span>
                        </label>
                        <div class="jm-combobox relative" data-combo="manual-dosen">
                            <input type="hidden" id="nipy" name="nipy" value="{{ $oldNipy }}">
                            <input type="hidden" id="nama_dosen" name="nama_dosen" value="{{ $oldNamaDosen }}">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                                    </svg>
                                </span>
                                <input
                                    type="text"
                                    id="jm-manual-dosen-search"
                                    value="{{ $oldNamaDosen ?: $oldNipy }}"
                                    autocomplete="off"
                                    placeholder="Cari Nama Dosen atau NIPY dari tb_dosen..."
                                    class="w-full rounded-xl border @error('nipy') border-rose-400 @else border-slate-200 @enderror bg-white py-2.5 pr-9 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/30 focus:outline-none"
                                >
                                <button type="button" data-combo-clear class="hidden absolute inset-y-0 right-0 items-center pr-3 text-slate-400 hover:text-rose-500" aria-label="Hapus pilihan">&times;</button>
                            </div>
                            <div data-combo-list class="hidden absolute z-30 mt-1.5 max-h-60 w-full overflow-y-auto rounded-xl border border-emerald-100 bg-white p-1.5 shadow-xl"></div>
                            <div id="jm-manual-dosen-badge" class="mt-2 hidden items-center justify-between gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3.5 py-2.5 text-xs"></div>
                        </div>
                        @error('nipy')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Footer Tombol Aksi --}}
                    <div class="flex flex-col-reverse items-stretch justify-between gap-3 border-t border-emerald-100 pt-5 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.jatah-makul.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            &larr; Kembali
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#20A464] px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#17804D]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>Simpan Jatah Makul</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
