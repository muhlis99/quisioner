@extends('layouts.mahasiswa')

@section('title', 'Menu Quisioner - Portal Mahasiswa')
@section('page-title', 'Daftar Quisioner')
@section('page-subtitle', 'Pilih quisioner aktif di bawah ini untuk mulai mengisi evaluasi EDOM maupun kuisioner umum')

@section('content')
    <div class="space-y-6">
        {{-- Filter Tab Tipe Quisioner --}}
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('mahasiswa.quisioner.index') }}" class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $filterType === '' ? 'bg-emerald-600 text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                Semua Quisioner
            </a>
            <a href="{{ route('mahasiswa.quisioner.index', ['type' => 'edom']) }}" class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $filterType === 'edom' ? 'bg-emerald-600 text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                Kuisioner EDOM
            </a>
            <a href="{{ route('mahasiswa.quisioner.index', ['type' => 'biasa']) }}" class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $filterType === 'biasa' ? 'bg-emerald-600 text-white shadow-xs' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                Kuisioner Umum
            </a>
        </div>

        {{-- Daftar Kartu Quisioner --}}
        @forelse ($items as $entry)
            @php
                $q = $entry['quisioner'];
                $isTuntas = $entry['is_tuntas'];
            @endphp

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase {{ $q->type === 'edom' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200' : 'bg-teal-50 text-teal-700 ring-1 ring-teal-200' }}">
                                {{ $q->type === 'edom' ? 'EDOM' : 'Kuisioner Umum' }}
                            </span>
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-slate-700">
                                TA {{ $q->tahun_ajaran }} &middot; Semester {{ $q->semester }}
                            </span>
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                {{ $q->pertanyaan_count }} pertanyaan
                            </span>
                        </div>

                        <h2 class="text-base font-bold text-slate-900 sm:text-lg">{{ $q->judul_quisioner }}</h2>

                        @if ($q->type === 'edom')
                            <p class="text-xs text-slate-500">
                                Progres pengisian mata kuliah/dosen:
                                <strong class="text-slate-800">{{ $entry['selesai_makul'] }} / {{ $entry['total_makul'] }}</strong> mata kuliah selesai.
                            </p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-3">
                        @if ($isTuntas)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3.5 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sudah Selesai (Tuntas)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3.5 py-1.5 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                Belum Selesai
                            </span>

                            @if ($q->type === 'biasa')
                                @if ($q->pertanyaan_count > 0)
                                    <a href="{{ route('mahasiswa.quisioner.show', $q) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700">
                                        <span>Isi Quisioner</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-medium text-slate-500">Belum ada soal</span>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Daftar Mata Kuliah / Dosen dari tb_jatah_makul (khusus EDOM) --}}
                @if ($q->type === 'edom')
                    <div class="border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Daftar Mata Kuliah &amp; Dosen Pengampu Anda
                        </p>

                        @if ($entry['makul_items']->isEmpty())
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-xs text-slate-500">
                                Belum ada data mata kuliah pada <code class="rounded bg-slate-100 px-1">tb_jatah_makul</code> untuk NIM Anda pada periode <strong>{{ $q->tahun_ajaran }} ({{ $q->semester }})</strong>.
                            </div>
                        @else
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                        <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                                            <tr>
                                                <th class="w-32 px-4 py-3">Kode</th>
                                                <th class="px-4 py-3">Mata Kuliah</th>
                                                <th class="px-4 py-3">Dosen Pengampu</th>
                                                <th class="w-36 px-4 py-3 text-center">Status</th>
                                                <th class="w-40 px-4 py-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach ($entry['makul_items'] as $mItem)
                                                @php $m = $mItem['makul']; @endphp
                                                <tr class="hover:bg-slate-50/80">
                                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-700">{{ $m->kode_makul }}</td>
                                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $m->nama_makul }}</td>
                                                    <td class="px-4 py-3 text-slate-700">
                                                        {{ $m->nama_dosen }}
                                                        <span class="block text-[11px] text-slate-400">NIPY: {{ $m->nipy }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if ($mItem['sudah_diisi'])
                                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                                                Selesai
                                                            </span>
                                                        @else
                                                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                                                Belum Diisi
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        @if ($mItem['sudah_diisi'])
                                                            <span class="text-xs font-medium text-slate-400">Sudah terkirim</span>
                                                        @elseif ($q->pertanyaan_count > 0)
                                                            <a href="{{ route('mahasiswa.quisioner.show', [$q, 'makul' => $m->id]) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                                <span>Isi Quisioner</span>
                                                                <span>&rarr;</span>
                                                            </a>
                                                        @else
                                                            <span class="text-xs text-slate-400">Belum ada soal</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-xs">
                <p class="text-sm font-semibold text-slate-700">Belum ada quisioner aktif saat ini.</p>
                <p class="mt-1 text-xs text-slate-500">Silakan cek kembali secara berkala saat periode pengisian EDOM atau kuisioner dibuka.</p>
            </div>
        @endforelse
    </div>
@endsection
