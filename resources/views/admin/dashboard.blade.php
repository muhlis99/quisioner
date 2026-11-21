@extends('layouts.admin')

@section('title', 'Dashboard Admin - Sistem Kuisioner')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Analitik interaktif kuisioner, EDOM, distribusi skor, dan ketuntasan mahasiswa')

@section('content')
    <div
        id="admin-dashboard-analytics"
        data-endpoint="{{ route('admin.dashboard.chart-data') }}"
        data-initial='@json($initialPayload)'
        class="space-y-6"
    >
        {{-- Banner & Filter Interaktif (AJAX / Fetch API) --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-[#062419] via-[#0B6B3A] to-[#20A464] px-6 py-5 text-white">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-emerald-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#20A464]"></span>
                            Analitik Real-Time
                        </span>
                        <h2 class="mt-1.5 text-lg font-bold tracking-tight sm:text-xl">Ringkasan Sistem Kuisioner &amp; EDOM</h2>
                    </div>
                    <div id="dashboard-loading" class="hidden items-center gap-2 rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-semibold text-white backdrop-blur-xs">
                        <span class="h-2.5 w-2.5 animate-ping rounded-full bg-emerald-200"></span>
                        <span>Memperbarui data...</span>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-50/40 p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-4">
                        <label for="filter-tahun-ajaran" class="block text-xs font-semibold text-emerald-950">Tahun Ajaran</label>
                        <select
                            id="filter-tahun-ajaran"
                            name="tahun_ajaran"
                            class="mt-1.5 block w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-2xs transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                        >
                            <option value="">Semua Tahun Ajaran</option>
                            @foreach ($tahunAjaranList as $ta)
                                <option value="{{ $ta }}">{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="filter-semester" class="block text-xs font-semibold text-emerald-950">Semester</label>
                        <select
                            id="filter-semester"
                            name="semester"
                            class="mt-1.5 block w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-2xs transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                        >
                            <option value="">Semua Semester</option>
                            <option value="ganjil">Ganjil</option>
                            <option value="genap">Genap</option>
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="filter-type" class="block text-xs font-semibold text-emerald-950">Tipe Quisioner</label>
                        <select
                            id="filter-type"
                            name="type"
                            class="mt-1.5 block w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-2xs transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="edom">EDOM</option>
                            <option value="biasa">Biasa</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <button
                            id="filter-reset-btn"
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
                <p id="dashboard-filter-error" class="mt-2.5 hidden text-xs font-medium text-rose-600"></p>
            </div>
        </div>

        {{-- 4 KPI Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="group rounded-xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:border-[#20A464]/40 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mahasiswa</p>
                        <p id="kpi-total-mahasiswa" class="mt-2.5 text-3xl font-extrabold tracking-tight text-[#062419] tabular-nums">
                            {{ number_format($initialPayload['kpi']['total_mahasiswa']) }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-100 transition group-hover:bg-[#20A464] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-500">Data terintegrasi SIAKAD</p>
            </div>

            <div class="group rounded-xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:border-[#20A464]/40 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Dosen</p>
                        <p id="kpi-total-dosen" class="mt-2.5 text-3xl font-extrabold tracking-tight text-[#062419] tabular-nums">
                            {{ number_format($initialPayload['kpi']['total_dosen']) }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-100 transition group-hover:bg-[#20A464] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-500">Dosen pengampu aktif</p>
            </div>

            <div class="group rounded-xl border border-emerald-100 bg-white p-5 shadow-sm transition hover:border-[#20A464]/40 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Quisioner Aktif</p>
                        <p id="kpi-total-quisioner" class="mt-2.5 text-3xl font-extrabold tracking-tight text-[#0B6B3A] tabular-nums">
                            {{ number_format($initialPayload['kpi']['total_quisioner']) }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-100 transition group-hover:bg-[#20A464] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-xs font-medium text-slate-500">Instrumen aktif sesuai filter</p>
            </div>

            <div class="group rounded-xl border border-emerald-200 bg-gradient-to-br from-white via-white to-emerald-50/80 p-5 shadow-sm transition hover:border-[#20A464] hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#0B6B3A]">Ketuntasan Pengisian</p>
                        <p id="kpi-persentase-ketuntasan" class="mt-2.5 text-3xl font-extrabold tracking-tight text-[#20A464] tabular-nums">
                            {{ number_format($initialPayload['kpi']['persentase_ketuntasan'], 1) }}%
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#20A464] text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p id="kpi-detail-ketuntasan" class="mt-3 text-xs font-medium text-slate-600">
                    {{ number_format($initialPayload['kpi']['total_tuntas']) }} dari {{ number_format($initialPayload['kpi']['total_ketuntasan']) }} data tuntas
                </p>
            </div>
        </div>

        {{-- Baris Grafik 1 & Grafik 2 --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            {{-- Grafik 1: Distribusi Skor Jawaban --}}
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm sm:p-6 xl:col-span-7">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-[#062419]">Distribusi Skor Jawaban</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Frekuensi pilihan skor responden (arah kursor untuk melihat persentase)</p>
                    </div>
                    <span id="badge-total-skor" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-[#0B6B3A] ring-1 ring-emerald-200/80">
                        {{ number_format($initialPayload['charts']['distribusi_skor']['total']) }} jawaban
                    </span>
                </div>

                <div id="wrap-chart-distribusi" class="relative h-72 {{ $initialPayload['charts']['distribusi_skor']['has_data'] ? '' : 'hidden' }}">
                    <canvas id="dashboardChartDistribusi"></canvas>
                </div>
                <div id="empty-chart-distribusi" class="{{ $initialPayload['charts']['distribusi_skor']['has_data'] ? 'hidden' : 'flex' }} h-72 flex-col items-center justify-center rounded-xl border border-dashed border-emerald-200 bg-[#F4F7F5] px-4 text-center">
                    <p class="text-sm font-semibold text-[#062419]">Belum Ada Data Distribusi Skor</p>
                    <p class="mt-1 max-w-sm text-xs text-slate-500">Tidak ditemukan jawaban pilihan ganda pada filter yang dipilih.</p>
                </div>
            </div>

            {{-- Grafik 2: Status Ketuntasan Mahasiswa --}}
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm sm:p-6 xl:col-span-5">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-[#062419]">Status Ketuntasan Mahasiswa</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Perbandingan mahasiswa sudah tuntas vs belum tuntas</p>
                    </div>
                    <span id="badge-total-ketuntasan" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-[#0B6B3A] ring-1 ring-emerald-200/80">
                        {{ number_format($initialPayload['charts']['ketuntasan']['total']) }} data
                    </span>
                </div>

                <div id="wrap-chart-ketuntasan" class="relative h-72 {{ $initialPayload['charts']['ketuntasan']['has_data'] ? '' : 'hidden' }}">
                    <canvas id="dashboardChartKetuntasan"></canvas>
                </div>
                <div id="empty-chart-ketuntasan" class="{{ $initialPayload['charts']['ketuntasan']['has_data'] ? 'hidden' : 'flex' }} h-72 flex-col items-center justify-center rounded-xl border border-dashed border-emerald-200 bg-[#F4F7F5] px-4 text-center">
                    <p class="text-sm font-semibold text-[#062419]">Belum Ada Data Ketuntasan</p>
                    <p class="mt-1 max-w-xs text-xs text-slate-500">Belum ada catatan ketuntasan pengisian pada filter yang dipilih.</p>
                </div>
            </div>
        </div>

        {{-- Grafik 3: Tren Partisipasi Harian --}}
        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-[#062419]">Tren Partisipasi Harian</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Perkembangan jumlah jawaban dan responden mahasiswa per tanggal</p>
                </div>
                <span id="badge-total-tren" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-[#0B6B3A] ring-1 ring-emerald-200/80">
                    {{ number_format($initialPayload['charts']['tren_harian']['total']) }} entri
                </span>
            </div>

            <div id="wrap-chart-tren" class="relative h-72 {{ $initialPayload['charts']['tren_harian']['has_data'] ? '' : 'hidden' }}">
                <canvas id="dashboardChartTren"></canvas>
            </div>
            <div id="empty-chart-tren" class="{{ $initialPayload['charts']['tren_harian']['has_data'] ? 'hidden' : 'flex' }} h-72 flex-col items-center justify-center rounded-xl border border-dashed border-emerald-200 bg-[#F4F7F5] px-4 text-center">
                <p class="text-sm font-semibold text-[#062419]">Belum Ada Aktivitas Pengisian Harian</p>
                <p class="mt-1 max-w-md text-xs text-slate-500">Grafik garis partisipasi harian akan otomatis muncul setelah mahasiswa mengirimkan jawaban kuisioner.</p>
            </div>
        </div>
    </div>
@endsection
