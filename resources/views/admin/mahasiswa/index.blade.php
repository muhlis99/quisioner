@extends('layouts.admin')

@section('title', 'Data Mahasiswa - Admin')
@section('page-title', 'Data Mahasiswa')
@section('page-subtitle', 'Daftar mahasiswa terintegrasi SIAKAD lengkap dengan pencarian dan sinkronisasi')

@section('content')
    <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        {{-- Toolbar Atas Card: Pencarian, Filter & Tombol Sinkronisasi Sejajar --}}
        <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between sm:px-6">
            <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
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
                        placeholder="Cari NIM atau nama mahasiswa..."
                        class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                    >
                </div>

                <select name="per_page" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                    @foreach ([10, 15, 25, 50, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }} / hal</option>
                    @endforeach
                </select>

                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                    <span>Cari</span>
                </button>

                @if ($search !== '')
                    <a href="{{ route('admin.mahasiswa.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>

            <div class="flex flex-wrap items-center justify-between gap-3 lg:justify-end">
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/70">
                    Total: {{ number_format($totalSemua) }} mahasiswa
                </span>

                <button
                    id="btn-sync-siakad-mahasiswa"
                    type="button"
                    data-url="{{ route('admin.mahasiswa.sync') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Sinkronisasi SIAKAD</span>
                </button>
            </div>
        </div>

        {{-- Tabel Data Mahasiswa --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                    <tr>
                        <th class="w-16 px-6 py-3.5">No</th>
                        <th class="w-48 px-6 py-3.5">NIM</th>
                        <th class="px-6 py-3.5">Nama Mahasiswa</th>
                        <th class="w-36 px-6 py-3.5 text-center">Sumber</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($mahasiswa as $index => $item)
                        <tr class="transition hover:bg-emerald-50/50">
                            <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $mahasiswa->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-[#0B6B3A]">{{ $item->nim }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->nama }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/80">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#20A464]"></span>
                                    SIAKAD
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">
                                @if ($search !== '')
                                    Tidak ditemukan data mahasiswa untuk kata kunci "<strong>{{ $search }}</strong>".
                                @else
                                    Belum ada data mahasiswa. Klik <strong>Sinkronisasi SIAKAD</strong> untuk menarik data dari server SIAKAD.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mahasiswa->hasPages())
            <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                {{ $mahasiswa->links() }}
            </div>
        @endif
    </div>
@endsection
