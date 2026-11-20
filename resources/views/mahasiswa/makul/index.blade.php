@extends('layouts.mahasiswa')

@section('title', 'Mata Kuliah Diambil - Portal Mahasiswa')
@section('page-title', 'Mata Kuliah Diambil')
@section('page-subtitle', 'Daftar mata kuliah dan dosen pengampu Anda dari tb_jatah_makul')

@section('content')
    <div class="space-y-5">
        {{-- Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('mahasiswa.makul.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-5">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari kode, nama mata kuliah, atau nama dosen..."
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    >
                </div>
                <div class="md:col-span-2">
                    <select name="tahun_ajaran" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none">
                        <option value="">Semua TA</option>
                        @foreach ($tahunAjaranList as $ta)
                            <option value="{{ $ta }}" @selected($tahunAjaran === $ta)>{{ $ta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="semester" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>
                </div>
                <div class="flex items-center justify-between gap-2 md:col-span-3 md:justify-end">
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Filter
                    </button>
                    @if ($search !== '' || $tahunAjaran !== '' || $semester !== '')
                        <a href="{{ route('mahasiswa.makul.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
            <p class="mt-3 text-xs text-slate-500">
                Total mata kuliah terdaftar untuk NIM Anda: <strong class="text-slate-800">{{ number_format($totalMakulSemua) }}</strong>
            </p>
        </div>

        {{-- Tabel Mata Kuliah --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-12 px-4 py-3.5">No</th>
                            <th class="w-32 px-4 py-3.5">Kode</th>
                            <th class="px-4 py-3.5">Mata Kuliah</th>
                            <th class="px-4 py-3.5">Dosen Pengampu</th>
                            <th class="w-36 px-4 py-3.5">Periode</th>
                            <th class="w-40 px-4 py-3.5 text-center">Status EDOM</th>
                            <th class="w-36 px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($makuls as $index => $item)
                            @php
                                $edomInfo = $edomStatusByMakulId[$item->id] ?? ['quisioner' => null, 'sudah_diisi' => false];
                                $edomQ = $edomInfo['quisioner'];
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 text-slate-500">{{ $makuls->firstItem() + $index }}</td>
                                <td class="px-4 py-3.5 font-mono text-xs font-semibold text-emerald-700">{{ $item->kode_makul }}</td>
                                <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $item->nama_makul }}</td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-slate-800">{{ $item->nama_dosen }}</p>
                                    <p class="font-mono text-[11px] text-slate-400">NIPY: {{ $item->nipy }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-slate-800">{{ $item->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if ($edomInfo['sudah_diisi'])
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            Sudah Diisi
                                        </span>
                                    @elseif ($edomQ)
                                        <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                            Belum Diisi
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                            Belum Ada EDOM
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    @if ($edomInfo['sudah_diisi'])
                                        <span class="text-xs font-medium text-slate-400">Selesai</span>
                                    @elseif ($edomQ && $edomQ->pertanyaan_count > 0)
                                        <a href="{{ route('mahasiswa.quisioner.show', [$edomQ, 'makul' => $item->id]) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                            <span>Isi EDOM</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data mata kuliah yang terdaftar untuk NIM Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($makuls->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $makuls->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
