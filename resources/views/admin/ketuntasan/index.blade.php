@extends('layouts.admin')

@section('title', 'Monitoring Ketuntasan - Admin')
@section('page-title', 'Monitoring Ketuntasan')
@section('page-subtitle', 'Pantau progres dan status penyelesaian pengisian kuisioner mahasiswa')

@section('content')
    <div class="space-y-6">
        {{-- Kartu Statistik Ringkas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Rekaman</p>
                <p class="mt-2 text-3xl font-extrabold text-[#062419] tabular-nums">{{ number_format($totalData) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sudah Tuntas</p>
                <p class="mt-2 text-3xl font-extrabold text-[#20A464] tabular-nums">{{ number_format($totalTuntas) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Belum Tuntas</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-600 tabular-nums">{{ number_format($totalBelum) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0B6B3A]">Persentase Ketuntasan</p>
                <p class="mt-2 text-3xl font-extrabold text-[#0B6B3A] tabular-nums">{{ number_format($persentase, 1) }}%</p>
            </div>
        </div>

        {{-- Card Tabel Monitoring Ketuntasan --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            {{-- Toolbar Filter Atas Card --}}
            <div class="border-b border-emerald-100 px-5 py-4 sm:px-6">
                <form method="GET" action="{{ route('admin.ketuntasan.index') }}" class="flex flex-wrap items-center gap-2.5">
                    <div class="relative min-w-[200px] flex-1 sm:max-w-xs">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari NIM atau nama mahasiswa..."
                            class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                        >
                    </div>

                    <select name="id_quisioner" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua Quisioner</option>
                        @foreach ($quisionerList as $q)
                            <option value="{{ $q->id }}" @selected($quisionerId === (string) $q->id)>
                                {{ $q->judul_quisioner }} ({{ $q->tahun_ajaran }})
                            </option>
                        @endforeach
                    </select>

                    <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>

                    <select name="ketuntasan" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($status === '1')>Tuntas</option>
                        <option value="0" @selected($status === '0')>Belum Tuntas</option>
                    </select>

                    <button type="submit" class="rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                        Filter
                    </button>

                    @if ($search !== '' || $quisionerId !== '' || $semester !== '' || $status !== '')
                        <a href="{{ route('admin.ketuntasan.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-14 px-6 py-3.5">No</th>
                            <th class="px-6 py-3.5">Mahasiswa</th>
                            <th class="px-6 py-3.5">Quisioner</th>
                            <th class="w-36 px-6 py-3.5">Periode</th>
                            <th class="w-44 px-6 py-3.5 text-center">Progres</th>
                            <th class="w-36 px-6 py-3.5 text-center">Status</th>
                            <th class="w-28 px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($ketuntasans as $index => $item)
                            @php
                                $target = max(1, (int) $item->status_insert_soal);
                                $selesai = (int) $item->status_penyelesaian;
                                $pct = min(100, (int) round(($selesai / $target) * 100));
                            @endphp
                            <tr class="transition hover:bg-emerald-50/50">
                                <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $ketuntasans->firstItem() + $index }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-mono text-xs font-semibold text-[#0B6B3A]">{{ $item->niim }}</p>
                                    <p class="mt-0.5 text-xs text-slate-600">{{ $item->mahasiswa->nama ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ $item->quisioner->judul_quisioner ?? 'Quisioner #' . $item->id_quisioner }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="font-semibold text-slate-900">{{ $item->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2.5">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full {{ $item->ketuntasan === '1' ? 'bg-[#20A464]' : 'bg-amber-500' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="font-mono text-xs text-slate-600">{{ $selesai }}/{{ $item->status_insert_soal }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($item->ketuntasan === '1')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/80">
                                            Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200/80">
                                            Belum Tuntas
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.ketuntasan.destroy', $item) }}"
                                        data-confirm-delete
                                        data-confirm-title="Hapus Data Ketuntasan?"
                                        data-confirm-text="Data ketuntasan untuk NIM {{ $item->niim }} akan dihapus."
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Belum ada data ketuntasan mahasiswa yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ketuntasans->hasPages())
                <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                    {{ $ketuntasans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
