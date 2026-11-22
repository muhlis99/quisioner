@extends('layouts.admin')

@section('title', 'Jatah Mata Kuliah - Admin')
@section('page-title', 'Jatah Mata Kuliah')
@section('page-subtitle', 'Rekap pemetaan mata kuliah dan dosen pengampu per mahasiswa untuk evaluasi EDOM')

@section('content')
    @php
        $hasFilter = $search !== '' || $semester !== '' || $tahunAjaran !== '' || $status !== '';
        $basePeriodQuery = array_filter([
            'search' => $search !== '' ? $search : null,
            'tahun_ajaran' => $tahunAjaran !== '' ? $tahunAjaran : null,
            'semester' => $semester !== '' ? $semester : null,
        ]);
    @endphp

    <div id="admin-jatah-makul-index" class="space-y-5">
        {{-- 4 Mini-Card Ringkasan Kompak (Klik untuk quick-filter status) --}}
        <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
            <a
                href="{{ route('admin.jatah-makul.index', $basePeriodQuery) }}"
                class="group rounded-2xl border bg-white p-4 shadow-2xs transition hover:-translate-y-0.5 hover:shadow-md {{ $status === '' ? 'border-[#20A464] ring-2 ring-[#20A464]/15' : 'border-emerald-100' }}"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Mahasiswa</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-[#0B6B3A]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-[#062419] tabular-nums">{{ number_format($summary['total_mahasiswa'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[11px] text-slate-500">Terdaftar di database</p>
            </a>

            <a
                href="{{ route('admin.jatah-makul.index', array_merge($basePeriodQuery, ['status' => 'sudah'])) }}"
                class="group rounded-2xl border bg-white p-4 shadow-2xs transition hover:-translate-y-0.5 hover:shadow-md {{ $status === 'sudah' ? 'border-[#20A464] ring-2 ring-[#20A464]/15' : 'border-emerald-100' }}"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Sudah Ada Makul</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100/80 text-[#20A464]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-[#0B6B3A] tabular-nums">{{ number_format($summary['sudah_ada_makul'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[11px] text-slate-500">Minimal 1 mata kuliah</p>
            </a>

            <a
                href="{{ route('admin.jatah-makul.index', array_merge($basePeriodQuery, ['status' => 'belum'])) }}"
                class="group rounded-2xl border bg-white p-4 shadow-2xs transition hover:-translate-y-0.5 hover:shadow-md {{ $status === 'belum' ? 'border-amber-400 ring-2 ring-amber-400/20' : 'border-amber-100' }}"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Belum Ada Makul</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-extrabold text-amber-700 tabular-nums">{{ number_format($summary['belum_ada_makul'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[11px] text-slate-500">0 mata kuliah terpetakan</p>
            </a>

            <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-[#062419] to-[#0B6B3A] p-4 text-white shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-200">Total Pemetaan</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-emerald-200">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-extrabold tabular-nums">{{ number_format($summary['total_pemetaan'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-[11px] text-emerald-100/80">
                    @if ($tahunAjaran !== '' || $semester !== '')
                        Filter: {{ $tahunAjaran !== '' ? $tahunAjaran : 'Semua TA' }}{{ $semester !== '' ? ' · ' . ucfirst($semester) : '' }}
                    @else
                        Seluruh jatah makul aktif
                    @endif
                </p>
            </div>
        </div>

        {{-- Card Utama Tabel Per-Mahasiswa --}}
        <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
            {{-- Toolbar Filter & Tombol Tambah --}}
            <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 xl:flex-row xl:items-center xl:justify-between sm:px-6">
                <form method="GET" action="{{ route('admin.jatah-makul.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
                    <div class="relative min-w-[220px] flex-1 sm:max-w-xs">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari NIM, mahasiswa, makul, atau dosen..."
                            class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                        >
                    </div>

                    <select name="tahun_ajaran" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua TA</option>
                        @foreach ($tahunAjaranList as $ta)
                            <option value="{{ $ta }}" @selected($tahunAjaran === $ta)>{{ $ta }}</option>
                        @endforeach
                    </select>

                    <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>

                    <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="sudah" @selected($status === 'sudah')>Sudah Ada Makul</option>
                        <option value="belum" @selected($status === 'belum')>Belum Ada Makul</option>
                    </select>

                    <button type="submit" class="rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                        Filter
                    </button>

                    @if ($hasFilter)
                        <a href="{{ route('admin.jatah-makul.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="flex shrink-0 items-center gap-2">
                    @if ($mahasiswas->count() > 0)
                        <button
                            type="button"
                            id="jm-toggle-all-btn"
                            data-expanded="false"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3.5 py-2 text-xs font-bold text-[#0B6B3A] transition hover:bg-emerald-100"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <span id="jm-toggle-all-text">Buka Semua</span>
                        </button>
                    @endif

                    <a href="{{ route('admin.jatah-makul.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Tambah Jatah Makul</span>
                    </a>
                </div>
            </div>

            {{-- Tabel Per-Mahasiswa dengan Expandable Accordion Row --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-14 px-6 py-3.5">No</th>
                            <th class="px-6 py-3.5">Mahasiswa</th>
                            <th class="px-6 py-3.5">Periode</th>
                            <th class="w-48 px-6 py-3.5 text-center">Jumlah Jatah Makul</th>
                            <th class="w-60 px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($mahasiswas as $index => $mhs)
                            @php
                                $totalMakul = (int) $mhs->total_makul;
                                $periodes = $mhs->jatahMakul
                                    ->map(fn ($jm) => trim((string) $jm->tahun_ajaran) . ' · ' . ucfirst((string) $jm->semester))
                                    ->unique()
                                    ->values();
                                $rowKey = 'mhs-' . $mhs->id;
                                $initialChar = mb_strtoupper(mb_substr($mhs->nama ?: $mhs->nim, 0, 1));
                            @endphp

                            {{-- Baris Utama Mahasiswa --}}
                            <tr class="jm-parent-row transition hover:bg-emerald-50/45" data-row-key="{{ $rowKey }}">
                                <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $mahasiswas->firstItem() + $index }}</td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-extrabold {{ $totalMakul > 0 ? 'bg-emerald-100 text-[#0B6B3A]' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $initialChar }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-mono text-xs font-bold text-[#0B6B3A]">{{ $mhs->nim }}</p>
                                            <p class="truncate font-semibold text-[#062419]">{{ $mhs->nama }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    @if ($periodes->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($periodes as $periodeLabel)
                                                <span class="inline-flex items-center rounded-lg bg-[#F4F7F5] px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-emerald-200/70">
                                                    {{ $periodeLabel }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs italic text-slate-400">
                                            Belum dipetakan
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if ($totalMakul > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-[#0B6B3A] ring-1 ring-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#20A464]"></span>
                                            {{ $totalMakul }} Mata Kuliah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            0 Mata Kuliah
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            data-jm-accordion-trigger="{{ $rowKey }}"
                                            aria-expanded="false"
                                            aria-controls="jm-detail-{{ $rowKey }}"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-xs font-bold text-[#0B6B3A] transition hover:bg-[#20A464] hover:text-white"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Lihat Makul</span>
                                            <svg data-jm-chevron class="h-3.5 w-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>

                                        <a
                                            href="{{ route('admin.jatah-makul.create', ['nim' => $mhs->nim]) }}"
                                            title="Tambah jatah mata kuliah untuk {{ $mhs->nama }}"
                                            class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-[#20A464] hover:bg-emerald-50 hover:text-[#0B6B3A]"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <span>Tambah</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            {{-- Expandable Accordion Detail Row --}}
                            <tr id="jm-detail-{{ $rowKey }}" data-jm-accordion-panel="{{ $rowKey }}" class="hidden bg-[#F4F7F5]/85">
                                <td colspan="5" class="px-5 py-4 sm:px-8">
                                    <div class="overflow-hidden rounded-xl border border-emerald-200/80 bg-white shadow-2xs">
                                        {{-- Header Panel Detail --}}
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-emerald-100 bg-emerald-50/70 px-4 py-2.5">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="font-bold text-[#062419]">Rincian Mata Kuliah — {{ $mhs->nama }}</span>
                                                <span class="rounded bg-white px-2 py-0.5 font-mono text-[11px] font-bold text-[#0B6B3A] ring-1 ring-emerald-200">{{ $mhs->nim }}</span>
                                            </div>
                                            <a
                                                href="{{ route('admin.jatah-makul.create', ['nim' => $mhs->nim]) }}"
                                                class="inline-flex items-center gap-1 rounded-lg bg-[#20A464] px-3 py-1 text-[11px] font-bold text-white transition hover:bg-[#17804D]"
                                            >
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                <span>Tambah Makul</span>
                                            </a>
                                        </div>

                                        @if ($totalMakul > 0)
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                                                    <thead class="bg-slate-50/90 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                                                        <tr>
                                                            <th class="w-12 px-4 py-2.5">No</th>
                                                            <th class="w-36 px-4 py-2.5">Periode</th>
                                                            <th class="px-4 py-2.5">Kode &amp; Nama Mata Kuliah</th>
                                                            <th class="px-4 py-2.5">Dosen Pengampu</th>
                                                            <th class="w-40 px-4 py-2.5 text-right">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 bg-white">
                                                        @foreach ($mhs->jatahMakul as $mkIdx => $item)
                                                            <tr class="transition hover:bg-emerald-50/40">
                                                                <td class="px-4 py-3 text-slate-500 tabular-nums">{{ $mkIdx + 1 }}</td>
                                                                <td class="px-4 py-3">
                                                                    <span class="font-bold text-slate-800">{{ $item->tahun_ajaran }}</span>
                                                                    <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <span class="inline-flex rounded-md bg-emerald-50 px-2 py-0.5 font-mono text-[11px] font-bold text-[#0B6B3A] ring-1 ring-emerald-200/70">
                                                                        {{ $item->kode_makul }}
                                                                    </span>
                                                                    <p class="mt-1 text-sm font-semibold text-[#062419]">{{ $item->nama_makul }}</p>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <p class="font-semibold text-slate-800">{{ $item->nama_dosen }}</p>
                                                                    <p class="font-mono text-[11px] text-slate-400">NIPY: {{ $item->nipy }}</p>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <div class="flex items-center justify-end gap-1.5">
                                                                        <a
                                                                            href="{{ route('admin.jatah-makul.edit', $item) }}"
                                                                            class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 transition hover:bg-amber-100"
                                                                        >
                                                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                                            </svg>
                                                                            <span>Edit</span>
                                                                        </a>
                                                                        <form
                                                                            method="POST"
                                                                            action="{{ route('admin.jatah-makul.destroy', $item) }}"
                                                                            data-confirm-delete
                                                                            data-confirm-title="Hapus Jatah Mata Kuliah?"
                                                                            data-confirm-text="Data {{ $item->nama_makul }} untuk NIM {{ $item->nim }} akan dihapus permanen."
                                                                        >
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 transition hover:bg-rose-100">
                                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                        @else
                                            {{-- Empty State Ringkas Jika 0 Mata Kuliah --}}
                                            <div class="flex flex-col items-center justify-center gap-2.5 px-6 py-7 text-center sm:flex-row sm:justify-between sm:text-left">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-200">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <p class="text-sm font-bold text-[#062419]">Belum Ada Jatah Mata Kuliah</p>
                                                        <p class="text-xs text-slate-500">
                                                            Mahasiswa <strong>{{ $mhs->nama }}</strong> ({{ $mhs->nim }}) belum memiliki pemetaan mata kuliah pada filter periode saat ini.
                                                        </p>
                                                    </div>
                                                </div>
                                                <a
                                                    href="{{ route('admin.jatah-makul.create', ['nim' => $mhs->nim]) }}"
                                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2 text-xs font-bold text-white shadow-2xs transition hover:bg-[#17804D]"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                    </svg>
                                                    <span>Tambah Jatah Makul</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Tidak ditemukan data mahasiswa yang sesuai dengan filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($mahasiswas->hasPages())
                <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                    {{ $mahasiswas->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
