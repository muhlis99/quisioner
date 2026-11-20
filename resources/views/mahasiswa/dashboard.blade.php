@extends('layouts.mahasiswa')

@section('title', 'Beranda Mahasiswa - Sistem Kuisioner')
@section('page-title', 'Beranda Mahasiswa')
@section('page-subtitle', 'Pantau daftar kuisioner EDOM, kuisioner umum, dan status ketuntasan Anda')

@section('content')
    <div class="space-y-6">
        {{-- Banner Sambutan --}}
        <div class="flex flex-col justify-between gap-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white shadow-sm sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-bold sm:text-xl">Selamat Datang di Portal Kuisioner Mahasiswa</h2>
                <p class="mt-1.5 max-w-2xl text-sm text-emerald-50">
                    Pastikan seluruh kuisioner EDOM untuk mata kuliah yang Anda ambil serta kuisioner umum pada semester aktif telah diisi hingga berstatus tuntas.
                </p>
            </div>
            <a href="{{ route('mahasiswa.quisioner.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-xs transition hover:bg-emerald-50">
                <span>Buka Menu Quisioner</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>

        {{-- Ringkasan Status Mahasiswa --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Mata Kuliah Terdaftar</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($jumlahMakul ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">Sumber: <code class="rounded bg-slate-100 px-1 py-0.5">tb_jatah_makul</code></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Quisioner Belum Tuntas</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($belumDiisi ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-400">Dari {{ number_format($totalAktif ?? 0) }} quisioner aktif</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Status Ketuntasan</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $statusKetuntasan ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-400">Total {{ number_format($totalJawaban ?? 0) }} jawaban tersimpan</p>
            </div>
        </div>
    </div>
@endsection
