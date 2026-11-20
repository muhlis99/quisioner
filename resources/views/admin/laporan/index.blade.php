@extends('layouts.admin')

@section('title', 'Hasil Laporan Quisioner - Admin')
@section('page-title', 'Hasil Laporan Quisioner')
@section('page-subtitle', 'Pilih quisioner untuk membuka rekapitulasi skor jawaban dan visualisasi grafik')

@section('content')
    <div class="space-y-5">
        {{-- Filter --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari judul quisioner atau tahun ajaran..."
                    class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none sm:max-w-md"
                >
                <select name="type" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                    <option value="">Semua Tipe</option>
                    <option value="edom" @selected($type === 'edom')>EDOM</option>
                    <option value="biasa" @selected($type === 'biasa')>Biasa</option>
                </select>
                <div class="flex items-center gap-2">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Cari
                    </button>
                    @if ($search !== '' || $type !== '')
                        <a href="{{ route('admin.laporan.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Daftar Quisioner untuk Laporan --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-14 px-5 py-3.5">No</th>
                            <th class="px-5 py-3.5">Judul Quisioner</th>
                            <th class="w-36 px-5 py-3.5">Periode</th>
                            <th class="w-24 px-5 py-3.5 text-center">Tipe</th>
                            <th class="w-32 px-5 py-3.5 text-center">Responden</th>
                            <th class="w-32 px-5 py-3.5 text-center">Total Jawaban</th>
                            <th class="w-44 px-5 py-3.5 text-right">Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($quisioners as $index => $item)
                            @php
                                $stat = $statsByQuisioner[$item->id] ?? ['responden' => 0, 'jawaban' => 0];
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-slate-500">{{ $quisioners->firstItem() + $index }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-900">{{ $item->judul_quisioner }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->pertanyaan_count }} pertanyaan</p>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-700">
                                    <span class="font-semibold">{{ $item->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">Semester {{ $item->semester }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase {{ $item->type === 'edom' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' }}">
                                        {{ $item->type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-800">
                                    {{ number_format($stat['responden']) }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-800">
                                    {{ number_format($stat['jawaban']) }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.laporan.show', $item) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>
                                        <span>Buka Laporan</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada quisioner aktif untuk ditampilkan laporannya.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($quisioners->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $quisioners->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
