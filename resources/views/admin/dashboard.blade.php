@extends('layouts.admin')

@section('title', 'Dashboard Admin - Sistem Kuisioner')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Ringkasan data kuisioner, EDOM, dan ketuntasan mahasiswa')

@section('content')
    <div class="space-y-6">
        {{-- Kartu Ringkasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Mahasiswa</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalMahasiswa ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-400">Sumber: <code class="rounded bg-slate-100 px-1 py-0.5">tb_mahasiswa</code></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Dosen</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalDosen ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-400">Sumber: <code class="rounded bg-slate-100 px-1 py-0.5">tb_dosen</code></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Kuisioner Aktif</p>
                <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $totalQuisioner ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-400">Sumber: <code class="rounded bg-slate-100 px-1 py-0.5">tb_quisioner</code></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Mahasiswa Tuntas</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $totalTuntas ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-400">Sumber: <code class="rounded bg-slate-100 px-1 py-0.5">tb_ketuntasan</code></p>
            </div>
        </div>

        {{-- Info Kesiapan Modul --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
            <h2 class="text-base font-bold text-slate-900">Layout Panel Admin Siap Digunakan</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                Halaman ini menggunakan <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-indigo-700">layouts.admin</code> dengan Sidebar dan Navbar responsif. Setelah kredensial MySQL Hostinger hPanel diisi pada file <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-indigo-700">.env</code>, data dari ke-9 Model Eloquent dapat langsung ditampilkan di modul masing-masing.
            </p>
        </div>
    </div>
@endsection
