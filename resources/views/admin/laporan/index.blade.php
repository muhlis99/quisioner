@extends('layouts.admin')

@section('title', 'Hasil Laporan Quisioner - Admin')
@section('page-title', 'Hasil Laporan Quisioner')
@section('page-subtitle', 'Pilih quisioner untuk membuka rekapitulasi skor jawaban dan visualisasi grafik')

@section('content')
    <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        {{-- Toolbar Atas Card --}}
        <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between sm:px-6">
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
                <div class="relative min-w-[220px] flex-1 sm:max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari judul quisioner atau tahun ajaran..."
                        class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                    >
                </div>

                <select name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                    <option value="">Semua Tipe</option>
                    <option value="edom" @selected($type === 'edom')>EDOM</option>
                    <option value="biasa" @selected($type === 'biasa')>Biasa</option>
                </select>

                <button type="submit" class="rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                    Cari
                </button>

                @if ($search !== '' || $type !== '')
                    <a href="{{ route('admin.laporan.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>

            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/70">
                {{ $quisioners->total() }} quisioner tersedia
            </span>
        </div>

        {{-- Tabel Daftar Quisioner untuk Laporan --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                    <tr>
                        <th class="w-14 px-6 py-3.5">No</th>
                        <th class="px-6 py-3.5">Judul Quisioner</th>
                        <th class="w-36 px-6 py-3.5">Periode</th>
                        <th class="w-28 px-6 py-3.5 text-center">Tipe</th>
                        <th class="w-32 px-6 py-3.5 text-center">Responden</th>
                        <th class="w-32 px-6 py-3.5 text-center">Total Jawaban</th>
                        <th class="w-44 px-6 py-3.5 text-right">Laporan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($quisioners as $index => $item)
                        @php
                            $stat = $statsByQuisioner[$item->id] ?? ['responden' => 0, 'jawaban' => 0];
                        @endphp
                        <tr class="transition hover:bg-emerald-50/50">
                            <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $quisioners->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900">{{ $item->judul_quisioner }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $item->pertanyaan_count }} pertanyaan</p>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700">
                                <span class="font-semibold text-slate-900">{{ $item->tahun_ajaran }}</span>
                                <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $item->type === 'edom' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/80' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/80' }}">
                                    {{ $item->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-800 tabular-nums">
                                {{ number_format($stat['responden']) }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-800 tabular-nums">
                                {{ number_format($stat['jawaban']) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.laporan.show', $item) }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#20A464] px-4 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                    <span>Lihat Laporan</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                Belum ada quisioner aktif untuk ditampilkan laporannya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quisioners->hasPages())
            <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                {{ $quisioners->links() }}
            </div>
        @endif
    </div>
@endsection
