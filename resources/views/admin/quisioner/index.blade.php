@extends('layouts.admin')

@section('title', 'Data Quisioner - Admin')
@section('page-title', 'Data Quisioner')
@section('page-subtitle', 'Kelola instrumen kuisioner aktif beserta butir pertanyaannya secara terstruktur')

@section('content')
    @php
        $hasServerFilter = $search !== '' || $semester !== '' || $type !== '';
        $baseQueryWithoutType = array_filter([
            'search' => $search !== '' ? $search : null,
            'semester' => $semester !== '' ? $semester : null,
        ]);
    @endphp

    <div
        id="admin-quisioner-index"
        data-reset-url="{{ route('admin.quisioner.index') }}"
        data-has-server-search="{{ $search !== '' ? '1' : '0' }}"
        class="space-y-6"
    >
        {{-- 1. RINGKASAN STATISTIK (4 KPI CARDS) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- KPI 1: Total Quisioner Aktif --}}
            <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Quisioner Aktif</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#062419] tabular-nums">
                            {{ number_format($kpi['total_quisioner'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-100 transition group-hover:bg-[#20A464] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-slate-500">Instrumen aktif tersedia untuk mahasiswa</p>
            </div>

            {{-- KPI 2: Instrumen EDOM --}}
            <div class="group relative overflow-hidden rounded-2xl border border-amber-100 bg-white p-5 shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Instrumen EDOM</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-700 tabular-nums">
                            {{ number_format($kpi['total_edom'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-200/80 transition group-hover:bg-amber-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-slate-500">Evaluasi dosen per mata kuliah</p>
            </div>

            {{-- KPI 3: Instrumen Biasa --}}
            <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#0B6B3A]">Instrumen Biasa</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#0B6B3A] tabular-nums">
                            {{ number_format($kpi['total_biasa'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#20A464] ring-1 ring-emerald-200/80 transition group-hover:bg-[#20A464] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-slate-500">Survei layanan &amp; evaluasi umum</p>
            </div>

            {{-- KPI 4: Total Butir Pertanyaan --}}
            <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-[#062419] via-[#0B6B3A] to-[#17804D] p-5 text-white shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Total Butir Pertanyaan</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-white tabular-nums">
                            {{ number_format($kpi['total_pertanyaan'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-emerald-200 ring-1 ring-white/20">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-emerald-100/85">Akumulasi soal pada seluruh instrumen aktif</p>
            </div>
        </div>

        {{-- 2. TOOLBAR FILTER INTERAKTIF, VIEW SWITCHER & CTA TAMBAH --}}
        <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-2xs sm:px-5">
            <div class="flex flex-col gap-3.5 xl:flex-row xl:items-center xl:justify-between">
                {{-- Kiri: Segmented Pill Filter Tipe --}}
                <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-slate-100/90 p-1 ring-1 ring-slate-200/70">
                    <a
                        href="{{ route('admin.quisioner.index', $baseQueryWithoutType) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $type === '' ? 'bg-[#0B6B3A] text-white shadow-xs' : 'text-slate-600 hover:bg-white hover:text-[#062419]' }}"
                    >
                        <span>Semua</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tabular-nums {{ $type === '' ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-700' }}">
                            {{ $typeCounts['all'] }}
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.quisioner.index', array_merge($baseQueryWithoutType, ['type' => 'edom'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $type === 'edom' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:bg-white hover:text-amber-700' }}"
                    >
                        <span>EDOM</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tabular-nums {{ $type === 'edom' ? 'bg-white/25 text-white' : 'bg-amber-100/80 text-amber-800' }}">
                            {{ $typeCounts['edom'] }}
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.quisioner.index', array_merge($baseQueryWithoutType, ['type' => 'biasa'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $type === 'biasa' ? 'bg-[#20A464] text-white shadow-xs' : 'text-slate-600 hover:bg-white hover:text-[#0B6B3A]' }}"
                    >
                        <span>Biasa</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tabular-nums {{ $type === 'biasa' ? 'bg-white/25 text-white' : 'bg-emerald-100/80 text-[#0B6B3A]' }}">
                            {{ $typeCounts['biasa'] }}
                        </span>
                    </a>
                </div>

                {{-- Kanan: Filter Semester, Pencarian Instan, View Switcher, + Tambah Quisioner --}}
                <div class="flex flex-1 flex-wrap items-center justify-end gap-2.5">
                    <form method="GET" action="{{ route('admin.quisioner.index') }}" id="quisioner-filter-form" class="flex flex-1 flex-wrap items-center justify-end gap-2 sm:flex-nowrap">
                        @if ($type !== '')
                            <input type="hidden" name="type" value="{{ $type }}">
                        @endif

                        <select
                            name="semester"
                            id="quisioner-semester-select"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                        >
                            <option value="">Semua Semester</option>
                            <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected($semester === 'genap')>Genap</option>
                        </select>

                        <div class="relative min-w-[200px] flex-1 sm:max-w-xs">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="quisioner-search-input"
                                name="search"
                                value="{{ $search }}"
                                autocomplete="off"
                                placeholder="Cari judul atau tahun ajaran..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                            >
                            <button
                                type="button"
                                id="quisioner-search-clear"
                                title="Bersihkan pencarian"
                                class="{{ $search !== '' ? 'flex' : 'hidden' }} absolute inset-y-0 right-2 my-auto h-6 w-6 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-700"
                            >
                                &times;
                            </button>
                        </div>

                        @if ($hasServerFilter)
                            <a
                                href="{{ route('admin.quisioner.index') }}"
                                class="inline-flex shrink-0 items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                            >
                                Reset
                            </a>
                        @endif
                    </form>

                    {{-- View Switcher: Grid Kartu vs Tabel --}}
                    <div class="inline-flex shrink-0 items-center rounded-xl bg-slate-100 p-1 ring-1 ring-slate-200/70" role="group" aria-label="Pilih mode tampilan">
                        <button
                            type="button"
                            data-quisioner-view-btn="grid"
                            aria-pressed="true"
                            title="Mode Kartu"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#20A464] px-2.5 py-1.5 text-xs font-bold text-white shadow-xs transition"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span class="hidden sm:inline">Kartu</span>
                        </button>
                        <button
                            type="button"
                            data-quisioner-view-btn="table"
                            aria-pressed="false"
                            title="Mode Tabel"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-600 transition hover:text-[#062419]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                            </svg>
                            <span class="hidden sm:inline">Tabel</span>
                        </button>
                    </div>

                    {{-- CTA Utama: + Tambah Quisioner --}}
                    <a
                        href="{{ route('admin.quisioner.create') }}"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-[#0B6B3A] to-[#20A464] px-4 py-2 text-xs font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-[#062419] hover:to-[#17804D] hover:shadow-md"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Quisioner</span>
                    </a>
                </div>
            </div>
        </div>

        @if ($quisioners->isNotEmpty())
            {{-- 3. MODE KARTU (DEFAULT — MODERN & MODIS) --}}
            <div id="quisioner-view-grid" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($quisioners as $item)
                    @php
                        $isEdom = $item->type === 'edom';
                        $soalCount = (int) $item->pertanyaan_count;
                        $searchBlob = mb_strtolower(trim($item->judul_quisioner . ' ' . $item->tahun_ajaran . ' ' . $item->semester . ' ' . $item->type));
                    @endphp

                    <article
                        data-quisioner-item
                        data-search-text="{{ $searchBlob }}"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-emerald-100/90 bg-white shadow-2xs transition-all duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg"
                    >
                        {{-- Garis Aksen Atas --}}
                        <div class="h-1.5 w-full bg-gradient-to-r {{ $isEdom ? 'from-amber-400 via-amber-500 to-orange-400' : 'from-[#0B6B3A] via-[#20A464] to-teal-500' }}"></div>

                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            {{-- Header: Badge Tipe + Periode --}}
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider {{ $isEdom ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/90' : 'bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-200/90' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdom ? 'bg-amber-500' : 'bg-[#20A464]' }}"></span>
                                    {{ $isEdom ? 'EDOM' : 'BIASA' }}
                                </span>

                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200/70">
                                    <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25" />
                                    </svg>
                                    <span>{{ $item->tahun_ajaran }} &bull; <span class="capitalize">{{ $item->semester }}</span></span>
                                </span>
                            </div>

                            {{-- Judul Quisioner --}}
                            <h3 class="mt-4 line-clamp-2 text-lg font-bold leading-snug text-slate-800 transition group-hover:text-[#0B6B3A]">
                                {{ $item->judul_quisioner }}
                            </h3>

                            {{-- Kotak Info Jumlah Pertanyaan --}}
                            <div class="mt-4">
                                @if ($soalCount > 0)
                                    <div class="flex items-center justify-between rounded-xl bg-emerald-50/65 px-3.5 py-2.5 ring-1 ring-emerald-100">
                                        <div class="flex items-center gap-2 text-xs font-bold text-[#0B6B3A]">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-[#20A464] shadow-2xs ring-1 ring-emerald-100">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                            <span>{{ number_format($soalCount, 0, ',', '.') }} Soal Tersedia</span>
                                        </div>
                                        <span class="text-[11px] font-semibold text-emerald-700/80">Siap diisi</span>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between rounded-xl bg-amber-50/80 px-3.5 py-2.5 ring-1 ring-amber-200/80">
                                        <div class="flex items-center gap-2 text-xs font-bold text-amber-800">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-amber-600 shadow-2xs ring-1 ring-amber-200">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </span>
                                            <span>0 Soal Tersedia</span>
                                        </div>
                                        <span class="text-[11px] font-semibold text-amber-700">Perlu diisi</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Footer Aksi 1 Baris Sejajar --}}
                            <div class="mt-auto flex items-center justify-between gap-2 border-t border-slate-100 pt-4">
                                <a
                                    href="{{ route('admin.quisioner.pertanyaan.index', $item) }}"
                                    class="group/btn inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-[#0B6B3A] ring-1 ring-emerald-200/80 transition hover:bg-[#20A464] hover:text-white hover:ring-[#20A464] whitespace-nowrap"
                                >
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    <span>Kelola Pertanyaan</span>
                                    <svg class="h-3.5 w-3.5 shrink-0 transition-transform duration-200 group-hover/btn:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>

                                <div class="flex shrink-0 items-center gap-1.5 whitespace-nowrap">
                                    <a
                                        href="{{ route('admin.quisioner.edit', $item) }}"
                                        title="Edit quisioner"
                                        class="inline-flex items-center gap-1 rounded-xl border border-amber-200 bg-amber-50 px-2.5 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.quisioner.destroy', $item) }}"
                                        data-confirm-delete
                                        data-confirm-title="Hapus Quisioner?"
                                        data-confirm-text="Quisioner '{{ $item->judul_quisioner }}' akan dinonaktifkan (hapus = 1) dan disembunyikan dari daftar."
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            title="Hapus quisioner"
                                            class="inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- 4. MODE TABEL (ALTERNATIF RINGKAS — AKSI 1 BARIS SEJAJAR) --}}
            <div id="quisioner-view-table" class="hidden overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                        <thead class="bg-emerald-50/90 text-xs font-bold uppercase tracking-wider text-emerald-900">
                            <tr>
                                <th class="w-12 px-5 py-3.5">No</th>
                                <th class="px-5 py-3.5">Judul Quisioner</th>
                                <th class="w-36 px-5 py-3.5 whitespace-nowrap">Periode</th>
                                <th class="w-24 px-5 py-3.5 text-center">Tipe</th>
                                <th class="w-32 px-5 py-3.5 text-center whitespace-nowrap">Pertanyaan</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($quisioners as $index => $item)
                                @php
                                    $isEdom = $item->type === 'edom';
                                    $soalCount = (int) $item->pertanyaan_count;
                                    $searchBlob = mb_strtolower(trim($item->judul_quisioner . ' ' . $item->tahun_ajaran . ' ' . $item->semester . ' ' . $item->type));
                                @endphp
                                <tr
                                    data-quisioner-item
                                    data-search-text="{{ $searchBlob }}"
                                    class="transition hover:bg-emerald-50/45"
                                >
                                    <td class="px-5 py-4 text-slate-500 tabular-nums">{{ $quisioners->firstItem() + $index }}</td>
                                    <td class="px-5 py-4 font-bold text-slate-800">{{ $item->judul_quisioner }}</td>
                                    <td class="px-5 py-4 text-xs whitespace-nowrap">
                                        <span class="font-bold text-slate-800">{{ $item->tahun_ajaran }}</span>
                                        <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-bold uppercase {{ $isEdom ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/80' : 'bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-200/80' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $isEdom ? 'bg-amber-500' : 'bg-[#20A464]' }}"></span>
                                            {{ $isEdom ? 'EDOM' : 'Biasa' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        @if ($soalCount > 0)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-[#0B6B3A] ring-1 ring-emerald-200/70 tabular-nums">
                                                {{ $soalCount }} soal
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200/80 tabular-nums">
                                                0 soal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                            <a
                                                href="{{ route('admin.quisioner.pertanyaan.index', $item) }}"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-[#0B6B3A] transition hover:bg-[#20A464] hover:text-white"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                </svg>
                                                <span>Pertanyaan</span>
                                            </a>

                                            <a
                                                href="{{ route('admin.quisioner.edit', $item) }}"
                                                class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 transition hover:bg-amber-100"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                                <span>Edit</span>
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('admin.quisioner.destroy', $item) }}"
                                                class="inline-flex"
                                                data-confirm-delete
                                                data-confirm-title="Hapus Quisioner?"
                                                data-confirm-text="Quisioner '{{ $item->judul_quisioner }}' akan dinonaktifkan (hapus = 1) dan disembunyikan dari daftar."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Empty State Dinamis Pencarian Instan Client-Side --}}
            <div id="quisioner-instant-empty" class="hidden rounded-2xl border border-dashed border-emerald-200 bg-white px-6 py-14 text-center shadow-2xs">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-[#20A464]">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#062419]">Quisioner Tidak Ditemukan</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Tidak ada instrumen quisioner yang cocok dengan kata kunci pencarian Anda di halaman ini.
                </p>
                <button
                    type="button"
                    id="quisioner-instant-reset-btn"
                    class="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#17804D]"
                >
                    <span>Reset Filter</span>
                </button>
            </div>

            @if ($quisioners->hasPages())
                <div class="rounded-2xl border border-emerald-100 bg-white px-5 py-3.5 shadow-2xs">
                    {{ $quisioners->links() }}
                </div>
            @endif
        @else
            {{-- 5. EMPTY STATE SERVER-SIDE --}}
            <div class="rounded-2xl border border-dashed border-emerald-200 bg-white px-6 py-16 text-center shadow-2xs">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-[#20A464]">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#062419]">
                    {{ $hasServerFilter ? 'Quisioner Tidak Ditemukan' : 'Belum Ada Data Quisioner Aktif' }}
                </h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    @if ($hasServerFilter)
                        Tidak ditemukan quisioner yang sesuai dengan kombinasi filter atau pencarian saat ini.
                    @else
                        Mulai buat instrumen quisioner pertama Anda untuk evaluasi akademik dan layanan kampus.
                    @endif
                </p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
                    @if ($hasServerFilter)
                        <a
                            href="{{ route('admin.quisioner.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            <span>Reset Filter</span>
                        </a>
                    @else
                        <a
                            href="{{ route('admin.quisioner.create') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#0B6B3A] to-[#20A464] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:from-[#062419] hover:to-[#17804D]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>+ Buat Quisioner Pertama</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
