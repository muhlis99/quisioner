@extends('layouts.admin')

@section('title', 'Hasil Laporan Quisioner - Admin')
@section('page-title', 'Hasil Laporan Quisioner')
@section('page-subtitle', 'Pantau partisipasi responden, rekapitulasi skor, dan visualisasi grafik evaluasi akademik')

@section('content')
    @php
        $hasServerFilter = $search !== '' || $type !== '';
        $searchQueryParam = $search !== '' ? ['search' => $search] : [];
    @endphp

    <div
        id="admin-laporan-index"
        data-reset-url="{{ route('admin.laporan.index') }}"
        data-has-server-search="{{ $search !== '' ? '1' : '0' }}"
        class="space-y-6"
    >
        {{-- 1. RINGKASAN EKSEKUTIF (4 KPI CARDS) --}}
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
                <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-amber-700 ring-1 ring-amber-200/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ $kpi['total_edom'] }} EDOM
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[#0B6B3A] ring-1 ring-emerald-200/70">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#20A464]"></span>
                        {{ $kpi['total_biasa'] }} Biasa
                    </span>
                </div>
            </div>

            {{-- KPI 2: Total Responden --}}
            <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Responden</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#0B6B3A] tabular-nums">
                            {{ number_format($kpi['total_responden'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#20A464] ring-1 ring-emerald-100 transition group-hover:bg-[#20A464] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    Akumulasi partisipasi dari <strong class="text-slate-700">{{ number_format($totalMahasiswa, 0, ',', '.') }}</strong> mahasiswa aktif
                </p>
            </div>

            {{-- KPI 3: Total Jawaban Masuk --}}
            <div class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white p-5 shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Jawaban Masuk</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#062419] tabular-nums">
                            {{ number_format($kpi['total_jawaban'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 ring-1 ring-teal-100 transition group-hover:bg-teal-600 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    Seluruh entri jawaban ganda &amp; esai terekam
                </p>
            </div>

            {{-- KPI 4: Periode Aktif / Terbaru --}}
            <div class="group relative overflow-hidden rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-[#062419] via-[#0B6B3A] to-[#17804D] p-5 text-white shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Periode Terbaru</p>
                        <p class="mt-2 truncate text-xl font-extrabold tracking-tight text-white">
                            {{ $kpi['periode_terbaru'] }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-emerald-200 ring-1 ring-white/20">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs text-emerald-100/85">
                    Periode evaluasi akademik aktif terkini
                </p>
            </div>
        </div>

        {{-- 2. TOOLBAR FILTER INTERAKTIF & VIEW SWITCHER --}}
        <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-2xs sm:px-5">
            <div class="flex flex-col gap-3.5 lg:flex-row lg:items-center lg:justify-between">
                {{-- Segmented Pill Filter Tipe --}}
                <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-slate-100/90 p-1 ring-1 ring-slate-200/70">
                    <a
                        href="{{ route('admin.laporan.index', $searchQueryParam) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $type === '' ? 'bg-[#0B6B3A] text-white shadow-xs' : 'text-slate-600 hover:bg-white hover:text-[#062419]' }}"
                    >
                        <span>Semua</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tabular-nums {{ $type === '' ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-700' }}">
                            {{ $typeCounts['all'] }}
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.laporan.index', array_merge($searchQueryParam, ['type' => 'edom'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $type === 'edom' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:bg-white hover:text-amber-700' }}"
                    >
                        <span>EDOM</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tabular-nums {{ $type === 'edom' ? 'bg-white/25 text-white' : 'bg-amber-100/80 text-amber-800' }}">
                            {{ $typeCounts['edom'] }}
                        </span>
                    </a>

                    <a
                        href="{{ route('admin.laporan.index', array_merge($searchQueryParam, ['type' => 'biasa'])) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all {{ $type === 'biasa' ? 'bg-[#20A464] text-white shadow-xs' : 'text-slate-600 hover:bg-white hover:text-[#0B6B3A]' }}"
                    >
                        <span>Biasa</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold tabular-nums {{ $type === 'biasa' ? 'bg-white/25 text-white' : 'bg-emerald-100/80 text-[#0B6B3A]' }}">
                            {{ $typeCounts['biasa'] }}
                        </span>
                    </a>
                </div>

                {{-- Pencarian Instan + View Switcher --}}
                <div class="flex flex-1 flex-wrap items-center justify-end gap-2.5 lg:max-w-xl">
                    <form method="GET" action="{{ route('admin.laporan.index') }}" id="laporan-search-form" class="relative flex-1 min-w-[220px]">
                        @if ($type !== '')
                            <input type="hidden" name="type" value="{{ $type }}">
                        @endif

                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>

                        <input
                            type="text"
                            id="laporan-search-input"
                            name="search"
                            value="{{ $search }}"
                            autocomplete="off"
                            placeholder="Cari judul quisioner atau tahun ajaran..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pr-9 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                        >

                        <button
                            type="button"
                            id="laporan-search-clear"
                            title="Bersihkan pencarian"
                            class="{{ $search !== '' ? 'flex' : 'hidden' }} absolute inset-y-0 right-2 my-auto h-6 w-6 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-700"
                        >
                            &times;
                        </button>
                    </form>

                    @if ($hasServerFilter)
                        <a
                            href="{{ route('admin.laporan.index') }}"
                            class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Reset
                        </a>
                    @endif

                    {{-- View Switcher: Grid Kartu vs Tabel --}}
                    <div class="inline-flex items-center rounded-xl bg-slate-100 p-1 ring-1 ring-slate-200/70" role="group" aria-label="Pilih mode tampilan">
                        <button
                            type="button"
                            data-laporan-view-btn="grid"
                            aria-pressed="true"
                            title="Tampilan Grid Kartu"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-[#20A464] px-3 py-1.5 text-xs font-bold text-white shadow-xs transition"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span class="hidden sm:inline">Kartu</span>
                        </button>
                        <button
                            type="button"
                            data-laporan-view-btn="table"
                            aria-pressed="false"
                            title="Tampilan Tabel Ringkas"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:text-[#062419]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                            </svg>
                            <span class="hidden sm:inline">Tabel</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($quisioners->isNotEmpty())
            {{-- 3. MODE KARTU (DEFAULT — MODERN & MODIS) --}}
            <div id="laporan-view-grid" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($quisioners as $item)
                    @php
                        $stat = $statsByQuisioner[$item->id] ?? ['responden' => 0, 'jawaban' => 0];
                        $isEdom = $item->type === 'edom';
                        $partisipasi = $totalMahasiswa > 0
                            ? min(100, (int) round(($stat['responden'] / $totalMahasiswa) * 100))
                            : 0;
                        $searchBlob = mb_strtolower(trim($item->judul_quisioner . ' ' . $item->tahun_ajaran . ' ' . $item->semester . ' ' . $item->type));
                    @endphp

                    <article
                        data-laporan-item
                        data-search-text="{{ $searchBlob }}"
                        class="group flex flex-col overflow-hidden rounded-2xl border border-emerald-100/90 bg-white shadow-2xs transition-all duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg"
                    >
                        {{-- Garis Aksen Atas --}}
                        <div class="h-1.5 w-full bg-gradient-to-r {{ $isEdom ? 'from-amber-400 via-amber-500 to-orange-400' : 'from-[#0B6B3A] via-[#20A464] to-teal-500' }}"></div>

                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            {{-- Header Kartu: Badge Tipe + Periode --}}
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider {{ $isEdom ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/90' : 'bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-200/90' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $isEdom ? 'bg-amber-500' : 'bg-[#20A464]' }}"></span>
                                    {{ $item->type }}
                                </span>

                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200/70">
                                    <svg class="h-3.5 w-3.5 text-[#20A464]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25" />
                                    </svg>
                                    <span>{{ $item->tahun_ajaran }} &bull; <span class="capitalize">{{ $item->semester }}</span></span>
                                </span>
                            </div>

                            {{-- Judul & Jumlah Pertanyaan --}}
                            <div class="mt-4">
                                <h3 class="line-clamp-2 text-lg font-bold leading-snug text-slate-800 transition group-hover:text-[#0B6B3A]">
                                    {{ $item->judul_quisioner }}
                                </h3>
                                <p class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                    <span>{{ number_format($item->pertanyaan_count, 0, ',', '.') }} Pertanyaan</span>
                                </p>
                            </div>

                            {{-- Mini-Stat Box Berdampingan --}}
                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-emerald-50/60 p-3 ring-1 ring-emerald-100/80">
                                    <p class="text-[11px] font-semibold text-slate-500">Responden</p>
                                    <p class="mt-1 text-xl font-extrabold text-[#062419] tabular-nums">
                                        {{ number_format($stat['responden'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-emerald-50/60 p-3 ring-1 ring-emerald-100/80">
                                    <p class="text-[11px] font-semibold text-slate-500">Total Jawaban</p>
                                    <p class="mt-1 text-xl font-extrabold text-[#0B6B3A] tabular-nums">
                                        {{ number_format($stat['jawaban'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Indikator Partisipasi --}}
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-[11px] font-semibold">
                                    <span class="text-slate-500">Tingkat Partisipasi</span>
                                    <span class="font-bold text-[#0B6B3A] tabular-nums">
                                        {{ $partisipasi }}%
                                        @if ($totalMahasiswa > 0)
                                            <span class="font-normal text-slate-400">({{ number_format($stat['responden'], 0, ',', '.') }}/{{ number_format($totalMahasiswa, 0, ',', '.') }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 {{ $isEdom ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-[#0B6B3A] to-[#20A464]' }}"
                                        style="width: {{ $partisipasi }}%"
                                    ></div>
                                </div>
                            </div>

                            {{-- Footer Tombol Gradien Full-Width --}}
                            <div class="mt-auto pt-5">
                                <a
                                    href="{{ route('admin.laporan.show', $item) }}"
                                    class="group/btn inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#0B6B3A] to-[#20A464] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:from-[#062419] hover:to-[#17804D] hover:shadow-md"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                    <span>Lihat Laporan &amp; Grafik</span>
                                    <svg class="h-4 w-4 transition-transform duration-200 group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- 4. MODE TABEL (ALTERNATIF RINGKAS) --}}
            <div id="laporan-view-table" class="hidden overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                        <thead class="bg-emerald-50/90 text-xs font-bold uppercase tracking-wider text-emerald-900">
                            <tr>
                                <th class="w-14 px-6 py-3.5">No</th>
                                <th class="px-6 py-3.5">Judul Quisioner</th>
                                <th class="w-40 px-6 py-3.5">Periode</th>
                                <th class="w-28 px-6 py-3.5 text-center">Tipe</th>
                                <th class="w-44 px-6 py-3.5">Partisipasi</th>
                                <th class="w-32 px-6 py-3.5 text-center">Total Jawaban</th>
                                <th class="w-44 px-6 py-3.5 text-right">Laporan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($quisioners as $index => $item)
                                @php
                                    $stat = $statsByQuisioner[$item->id] ?? ['responden' => 0, 'jawaban' => 0];
                                    $isEdom = $item->type === 'edom';
                                    $partisipasi = $totalMahasiswa > 0
                                        ? min(100, (int) round(($stat['responden'] / $totalMahasiswa) * 100))
                                        : 0;
                                    $searchBlob = mb_strtolower(trim($item->judul_quisioner . ' ' . $item->tahun_ajaran . ' ' . $item->semester . ' ' . $item->type));
                                @endphp
                                <tr
                                    data-laporan-item
                                    data-search-text="{{ $searchBlob }}"
                                    class="transition hover:bg-emerald-50/45"
                                >
                                    <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $quisioners->firstItem() + $index }}</td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ $item->judul_quisioner }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ number_format($item->pertanyaan_count, 0, ',', '.') }} Pertanyaan</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <span class="font-bold text-slate-800">{{ $item->tahun_ajaran }}</span>
                                        <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-bold uppercase {{ $isEdom ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/80' : 'bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-200/80' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $isEdom ? 'bg-amber-500' : 'bg-[#20A464]' }}"></span>
                                            {{ $item->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-between text-xs font-semibold">
                                            <span class="text-slate-700 tabular-nums">{{ number_format($stat['responden'], 0, ',', '.') }} resp.</span>
                                            <span class="text-[#0B6B3A] tabular-nums">{{ $partisipasi }}%</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                class="h-full rounded-full {{ $isEdom ? 'bg-amber-500' : 'bg-[#20A464]' }}"
                                                style="width: {{ $partisipasi }}%"
                                            ></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-[#062419] tabular-nums">
                                        {{ number_format($stat['jawaban'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a
                                            href="{{ route('admin.laporan.show', $item) }}"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#0B6B3A] to-[#20A464] px-3.5 py-2 text-xs font-bold text-white shadow-2xs transition hover:from-[#062419] hover:to-[#17804D]"
                                        >
                                            <span>Lihat Laporan</span>
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Empty State Dinamis untuk Pencarian Instan Client-Side --}}
            <div id="laporan-instant-empty" class="hidden rounded-2xl border border-dashed border-emerald-200 bg-white px-6 py-14 text-center shadow-2xs">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-[#20A464]">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#062419]">Quisioner Tidak Ditemukan</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    Tidak ada laporan quisioner yang cocok dengan kata kunci pencarian Anda di halaman ini.
                </p>
                <button
                    type="button"
                    id="laporan-instant-reset-btn"
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-[#062419]">Belum Ada Laporan Quisioner yang Cocok</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                    @if ($hasServerFilter)
                        Tidak ditemukan quisioner aktif yang sesuai dengan filter atau kata kunci Anda. Coba atur ulang filter untuk melihat seluruh daftar laporan.
                    @else
                        Belum ada quisioner aktif yang tersedia untuk ditampilkan laporannya saat ini.
                    @endif
                </p>
                @if ($hasServerFilter)
                    <a
                        href="{{ route('admin.laporan.index') }}"
                        class="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#17804D]"
                    >
                        <span>Reset Filter</span>
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection
