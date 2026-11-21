@extends('layouts.mahasiswa')

@section('title', 'Beranda Mahasiswa - Sistem Kuisioner')
@section('page-title', 'Beranda Mahasiswa')
@section('page-subtitle', 'Pantau progres ketuntasan EDOM dan kuisioner umum Anda secara langsung')

@section('content')
    <div id="mahasiswa-dashboard-root" class="space-y-6">
        {{-- 1. Hero Banner Biru Gradien & Circular Progress Visual --}}
        <section
            id="mahasiswa-dashboard-hero"
            data-progress-target="{{ $persentaseKetuntasan }}"
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F294A] via-[#1E3A8A] to-[#2563EB] p-6 text-white shadow-md sm:p-7"
        >
            {{-- Dekorasi Latar Halus & Watermark Logo Kampus --}}
            <div class="pointer-events-none absolute -top-16 -right-16 h-60 w-60 rounded-full bg-[#60A5FA]/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <img
                src="{{ asset('assets/img/logo-stainaa.png') }}"
                alt=""
                aria-hidden="true"
                class="pointer-events-none absolute -right-10 -bottom-12 h-52 w-52 select-none object-contain opacity-[0.08] sm:h-60 sm:w-60"
            >

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                {{-- Sapaan Personal & Periode Aktif --}}
                <div class="max-w-2xl space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-blue-50 ring-1 ring-white/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#60A5FA]"></span>
                            NIM: <span class="font-mono">{{ $nimMahasiswa }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0F294A]/50 px-3 py-1 text-xs font-semibold capitalize text-blue-100 ring-1 ring-white/15">
                            TA {{ $tahunAjaranAktif }} &middot; Semester {{ $semesterAktif }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight sm:text-2xl">
                            Halo, {{ $namaMahasiswa }}
                        </h2>
                        <p class="mt-1.5 text-sm leading-relaxed text-blue-100/90">
                            Pantau seluruh tanggungan kuisioner EDOM dan kuisioner umum Anda di satu tempat. Lengkapi seluruh instrumen aktif agar status akademik Anda tuntas 100%.
                        </p>
                    </div>

                    {{-- Linear Progress Bar dengan Animasi Halus --}}
                    <div class="pt-1">
                        <div class="mb-1.5 flex items-center justify-between text-xs font-semibold text-blue-50">
                            <span id="mhs-hero-progress-text">
                                {{ number_format($sudahTuntas) }} dari {{ number_format($totalWajib) }} Quisioner Tuntas &mdash; {{ $persentaseKetuntasan }}%
                            </span>
                            <span class="hidden sm:inline text-blue-100/90">{{ number_format($jumlahMakul) }} Mata Kuliah Terdaftar</span>
                        </div>
                        <div class="h-2.5 w-full max-w-lg overflow-hidden rounded-full bg-[#0F294A]/50 ring-1 ring-white/15">
                            <div
                                id="mhs-linear-progress"
                                data-target-width="{{ $persentaseKetuntasan }}"
                                class="h-full rounded-full bg-gradient-to-r from-[#60A5FA] to-sky-300 transition-all duration-700 ease-out"
                                style="width: 0%"
                            ></div>
                        </div>
                    </div>
                </div>

                {{-- Circular Progress Visual Interaktif --}}
                <div class="flex shrink-0 items-center gap-4 self-start rounded-2xl bg-[#0F294A]/40 px-5 py-4 ring-1 ring-white/15 backdrop-blur-xs lg:self-center">
                    <div class="relative flex h-24 w-24 items-center justify-center">
                        <svg class="h-24 w-24 -rotate-90" viewBox="0 0 100 100" aria-hidden="true">
                            <circle
                                cx="50"
                                cy="50"
                                r="42"
                                fill="none"
                                stroke="rgba(255, 255, 255, 0.18)"
                                stroke-width="9"
                            />
                            <circle
                                id="mhs-circular-progress"
                                cx="50"
                                cy="50"
                                r="42"
                                fill="none"
                                stroke="#60A5FA"
                                stroke-width="9"
                                stroke-linecap="round"
                                stroke-dasharray="263.89"
                                stroke-dashoffset="263.89"
                                data-target-percent="{{ $persentaseKetuntasan }}"
                                style="transition: stroke-dashoffset 850ms cubic-bezier(0.22, 1, 0.36, 1);"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span id="mhs-circular-label" class="text-xl font-extrabold tracking-tight text-white tabular-nums">
                                {{ $persentaseKetuntasan }}%
                            </span>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-blue-200">Tuntas</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-200">Progres Semester</p>
                        <p class="text-base font-bold text-white tabular-nums">
                            {{ number_format($sudahTuntas) }} / {{ number_format($totalWajib) }} Selesai
                        </p>
                        @if ($belumTuntas > 0)
                            <p class="text-xs text-amber-200">
                                Tersisa {{ number_format($belumTuntas) }} belum diisi
                            </p>
                        @else
                            <p class="text-xs text-emerald-200">
                                {{ $totalWajib > 0 ? 'Seluruh tanggungan selesai!' : 'Belum ada tanggungan aktif' }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Banner Apresiasi 100% Tuntas (Hijau Emerald Lembut) --}}
        @if ($isAllTuntas)
            <div
                id="mhs-all-tuntas-banner"
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-950 shadow-xs sm:flex-row sm:items-center sm:px-6"
            >
                <div class="flex items-start gap-3.5 sm:items-center">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-950 sm:text-base">
                            Semua Quisioner Semester Ini Telah Tuntas
                        </h3>
                        <p class="mt-0.5 text-xs text-emerald-800">
                            Terima kasih, {{ $namaMahasiswa }}! Seluruh {{ number_format($totalWajib) }} tanggungan EDOM &amp; kuisioner Anda telah tersimpan dengan lengkap.
                        </p>
                    </div>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-2xs">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    100% Tuntas
                </span>
            </div>
        @endif

        {{-- 2. Kartu Ringkasan Status (3 KPI Cards Interaktif & Dapat Diklik untuk Filter) --}}
        <section aria-label="Ringkasan status ketuntasan" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- KPI 1: Total Quisioner / EDOM Wajib --}}
            <button
                type="button"
                data-kpi-filter=""
                aria-pressed="true"
                class="mhs-kpi-card group flex w-full flex-col justify-between rounded-2xl border-2 border-[#2563EB] bg-white p-5 text-left shadow-xs ring-2 ring-[#2563EB]/15 transition duration-200 hover:-translate-y-0.5 hover:border-[#2563EB] hover:shadow-md focus:outline-none"
            >
                <div class="flex w-full items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Quisioner / EDOM Wajib</p>
                        <p class="mt-2 text-3xl font-extrabold text-[#0F294A] tabular-nums">{{ number_format($totalWajib) }}</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#2563EB] ring-1 ring-blue-100 transition group-hover:bg-[#2563EB] group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 flex items-center justify-between text-xs font-medium text-slate-500">
                    <span>Klik untuk tampilkan semua</span>
                    <span class="font-semibold text-[#2563EB]">Semua &rarr;</span>
                </p>
            </button>

            {{-- KPI 2: Sudah Tuntas --}}
            <button
                type="button"
                data-kpi-filter="1"
                aria-pressed="false"
                class="mhs-kpi-card group flex w-full flex-col justify-between rounded-2xl border border-blue-100 bg-white p-5 text-left shadow-xs transition duration-200 hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-md focus:outline-none"
            >
                <div class="flex w-full items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Sudah Tuntas</p>
                        <p class="mt-2 text-3xl font-extrabold text-emerald-600 tabular-nums">{{ number_format($sudahTuntas) }}</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200/80 transition group-hover:bg-emerald-600 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 flex items-center justify-between text-xs font-medium text-slate-500">
                    <span>Klik untuk filter sudah tuntas</span>
                    <span class="font-semibold text-emerald-600">Tuntas &rarr;</span>
                </p>
            </button>

            {{-- KPI 3: Belum Tuntas --}}
            <button
                type="button"
                data-kpi-filter="0"
                aria-pressed="false"
                class="mhs-kpi-card group flex w-full flex-col justify-between rounded-2xl border border-amber-200/80 bg-gradient-to-br from-white via-white to-amber-50/50 p-5 text-left shadow-xs transition duration-200 hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md focus:outline-none"
            >
                <div class="flex w-full items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Belum Tuntas</p>
                        <p class="mt-2 text-3xl font-extrabold text-amber-600 tabular-nums">{{ number_format($belumTuntas) }}</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100/80 text-amber-700 ring-1 ring-amber-200 transition group-hover:bg-amber-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 flex items-center justify-between text-xs font-medium text-slate-600">
                    <span>Klik untuk filter belum diisi</span>
                    <span class="font-semibold text-amber-700">Belum Diisi &rarr;</span>
                </p>
            </button>
        </section>

        {{-- 3. Panel Status Ketuntasan Interaktif --}}
        <section id="mahasiswa-ketuntasan-section" class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xs">
            {{-- Header Panel --}}
            <div class="border-b border-blue-100 px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#0F294A]">Daftar Status Ketuntasan Quisioner &amp; EDOM</h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Gabungan seluruh kuisioner aktif dan mata kuliah EDOM yang wajib Anda isi. Klik <strong>Isi Sekarang</strong> untuk langsung mengisi.
                        </p>
                    </div>
                    <span id="mhs-visible-counter" class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-[#1E3A8A] ring-1 ring-blue-200/80 tabular-nums">
                        Menampilkan {{ number_format($totalWajib) }} item
                    </span>
                </div>
            </div>

            {{-- Kontrol Filter Cepat (Client-side JS Murni, Tanpa Reload) --}}
            <div class="flex flex-col gap-3 border-b border-blue-100 bg-blue-50/40 px-5 py-4 xl:flex-row xl:items-center xl:justify-between sm:px-6">
                {{-- Tab Filter Status dengan Counter --}}
                <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter status ketuntasan">
                    <button
                        type="button"
                        data-mhs-status=""
                        class="mhs-status-tab inline-flex items-center gap-1.5 rounded-full bg-[#0F294A] px-3.5 py-1.5 text-xs font-semibold text-white shadow-2xs transition"
                    >
                        <span>Semua</span>
                        <span id="mhs-count-all" class="rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold tabular-nums">{{ $totalWajib }}</span>
                    </button>
                    <button
                        type="button"
                        data-mhs-status="0"
                        class="mhs-status-tab inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50"
                    >
                        <span>Belum Diisi</span>
                        <span id="mhs-count-belum" class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 tabular-nums">{{ $belumTuntas }}</span>
                    </button>
                    <button
                        type="button"
                        data-mhs-status="1"
                        class="mhs-status-tab inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"
                    >
                        <span>Sudah Tuntas</span>
                        <span id="mhs-count-tuntas" class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 tabular-nums">{{ $sudahTuntas }}</span>
                    </button>
                </div>

                {{-- Filter Tipe & Pencarian Instan --}}
                <div class="flex flex-1 flex-wrap items-center gap-2.5 xl:justify-end">
                    <select
                        id="mhs-filter-type"
                        aria-label="Filter tipe quisioner"
                        class="rounded-xl border border-blue-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="edom">EDOM</option>
                        <option value="biasa">Biasa</option>
                    </select>

                    <div class="relative min-w-[220px] flex-1 sm:max-w-sm">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>
                        <input
                            id="mhs-search-quisioner"
                            type="text"
                            placeholder="Cari judul quisioner, mata kuliah, atau dosen..."
                            class="w-full rounded-xl border border-blue-200 bg-white py-2 pr-3.5 pl-9 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none"
                        >
                    </div>
                </div>
            </div>

            {{-- Card Grid Responsif Daftar Status Ketuntasan --}}
            <div class="p-5 sm:p-6">
                @if ($checklistItems->isEmpty())
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-blue-200 bg-[#F4F7FB] px-6 py-12 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100/80 text-[#2563EB]">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <h4 class="mt-4 text-sm font-bold text-[#0F294A]">Tidak Ada Tanggungan Quisioner Saat Ini</h4>
                        <p class="mt-1 max-w-md text-xs text-slate-500">
                            Belum ada instrumen kuisioner atau EDOM aktif untuk semester ini. Silakan periksa kembali secara berkala.
                        </p>
                    </div>
                @else
                    <div id="mhs-kt-grid" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach ($checklistItems as $item)
                            @php
                                $isTuntas = $item['is_tuntas'];
                                $isEdom = $item['type'] === 'edom';
                            @endphp
                            <article
                                class="mhs-kt-item flex flex-col justify-between rounded-2xl border p-5 transition duration-200 {{ $isTuntas ? 'border-emerald-200/80 bg-emerald-50/25' : 'border-blue-100 bg-white shadow-2xs hover:-translate-y-0.5 hover:border-[#3B82F6]/60 hover:shadow-md' }}"
                                data-status="{{ $isTuntas ? '1' : '0' }}"
                                data-type="{{ $item['type'] }}"
                                data-search="{{ $item['search_text'] }}"
                            >
                                {{-- Bagian Atas Kartu --}}
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase {{ $isEdom ? 'bg-blue-50 text-[#1E3A8A] ring-1 ring-blue-200' : 'bg-sky-50 text-sky-700 ring-1 ring-sky-200' }}">
                                                {{ $isEdom ? 'EDOM' : 'Biasa' }}
                                            </span>
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium capitalize text-slate-600">
                                                TA {{ $item['tahun_ajaran'] }} &middot; Sem. {{ $item['semester'] }}
                                            </span>
                                        </div>

                                        @if ($isTuntas)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">
                                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Tuntas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Belum Diisi
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Judul Quisioner / Mata Kuliah --}}
                                    @if ($isEdom && ! $item['makul_missing'])
                                        <h4 class="mt-3 text-base font-bold text-[#0F294A]">
                                            {{ $item['nama_makul'] }}
                                        </h4>
                                        <div class="mt-1.5 space-y-1 text-xs text-slate-600">
                                            <p class="flex items-center gap-1.5">
                                                <span class="rounded bg-blue-50 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-[#1E3A8A]">{{ $item['kode_makul'] }}</span>
                                                <span class="truncate font-medium text-slate-700">{{ $item['nama_dosen'] }}</span>
                                            </p>
                                            <p class="text-[11px] text-slate-400">
                                                Instrumen: {{ $item['judul'] }}
                                            </p>
                                        </div>
                                    @else
                                        <h4 class="mt-3 text-base font-bold text-[#0F294A]">
                                            {{ $item['judul'] }}
                                        </h4>
                                        @if ($item['makul_missing'])
                                            <p class="mt-1 text-xs text-slate-500">
                                                Belum ada data mata kuliah terdaftar pada periode ini.
                                            </p>
                                        @else
                                            <p class="mt-1 text-xs text-slate-500">
                                                Kuisioner umum &middot; {{ number_format($item['pertanyaan_count']) }} pertanyaan
                                            </p>
                                        @endif
                                    @endif
                                </div>

                                {{-- Bagian Footer Aksi Kartu --}}
                                <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3.5">
                                    <span class="text-xs text-slate-500">
                                        {{ number_format($item['pertanyaan_count']) }} butir soal
                                    </span>

                                    @if ($isTuntas)
                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-100/80 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            Selesai &amp; Terkunci
                                        </span>
                                    @elseif ($item['fill_url'])
                                        <a
                                            href="{{ $item['fill_url'] }}"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#1D4ED8]"
                                        >
                                            <span>Isi Sekarang</span>
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    @else
                                        <span class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500">
                                            Belum ada soal
                                        </span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Empty State Dinamis --}}
                    <div id="mhs-kt-empty-filter" class="hidden flex-col items-center justify-center rounded-2xl border border-dashed border-blue-200 bg-[#F4F7FB] px-6 py-12 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100/80 text-[#2563EB]">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <h4 id="mhs-empty-title" class="mt-4 text-sm font-bold text-[#0F294A]">Tidak ada quisioner yang sesuai filter</h4>
                        <p id="mhs-empty-desc" class="mt-1 max-w-md text-xs text-slate-500">
                            Coba ubah kata kunci pencarian, tipe quisioner, atau tab status untuk melihat daftar lainnya.
                        </p>
                        <button
                            id="mhs-reset-filter-btn"
                            type="button"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1D4ED8]"
                        >
                            Tampilkan Semua Quisioner
                        </button>
                    </div>
                @endif
            </div>
        </section>

        {{-- Riwayat Catatan Ketuntasan (tb_ketuntasan) --}}
        @if ($riwayatKetuntasan->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xs">
                <div class="border-b border-blue-100 px-5 py-4 sm:px-6">
                    <h3 class="text-sm font-bold text-[#0F294A]">Riwayat Catatan Ketuntasan</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Rekaman historis penyelesaian kuisioner yang tercatat pada akun Anda.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100/80 text-left text-sm">
                        <thead class="bg-blue-50 text-xs font-bold uppercase tracking-wider text-[#0F294A]">
                            <tr>
                                <th class="w-12 px-5 py-3">No</th>
                                <th class="px-5 py-3">Quisioner</th>
                                <th class="w-40 px-5 py-3">Periode</th>
                                <th class="w-32 px-5 py-3 text-center">Penyelesaian</th>
                                <th class="w-32 px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($riwayatKetuntasan as $idx => $rec)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-5 py-3 text-xs text-slate-500 tabular-nums">{{ $idx + 1 }}</td>
                                    <td class="px-5 py-3 font-medium text-[#0F294A]">
                                        {{ $quisionerTitles[$rec->id_quisioner] ?? 'Quisioner #' . $rec->id_quisioner }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-700">
                                        {{ $rec->tahun_ajaran }} &middot; <span class="capitalize">{{ $rec->semester }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center font-mono text-xs text-slate-700">
                                        {{ $rec->status_penyelesaian }} / {{ $rec->status_insert_soal }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($rec->ketuntasan === '1')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                                Tuntas
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                                Belum Tuntas
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
