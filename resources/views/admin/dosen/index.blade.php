@extends('layouts.admin')

@section('title', 'Data Dosen - Admin')
@section('page-title', 'Data Dosen')
@section('page-subtitle', 'Integrasi data dosen dari database SIAKAD (tb_dosen)')

@section('content')
    <div class="space-y-5">
        {{-- Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('admin.dosen.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1 sm:max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari NIPY atau nama dosen..."
                            class="w-full rounded-xl border border-slate-300 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                        >
                    </div>

                    <select name="per_page" onchange="this.form.submit()" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        @foreach ([10, 15, 25, 50, 100] as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }} / halaman</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            Cari
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('admin.dosen.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

                <div class="text-xs font-medium text-slate-500">
                    Total Dosen: <span class="font-bold text-slate-800">{{ number_format($totalSemua) }}</span>
                </div>
            </form>
        </div>

        {{-- Tabel Data Dosen --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-16 px-5 py-3.5">No</th>
                            <th class="w-48 px-5 py-3.5">NIPY</th>
                            <th class="px-5 py-3.5">Nama Dosen</th>
                            <th class="w-36 px-5 py-3.5 text-center">Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($dosen as $index => $item)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-slate-500">{{ $dosen->firstItem() + $index }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-indigo-700">{{ $item->nipy }}</td>
                                <td class="px-5 py-3.5 font-medium text-slate-900">{{ $item->nama }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200/70">
                                        SIAKAD
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">
                                    @if ($search !== '')
                                        Tidak ditemukan data dosen untuk kata kunci "<strong>{{ $search }}</strong>".
                                    @else
                                        Belum ada data dosen pada tabel <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">tb_dosen</code>.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($dosen->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $dosen->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
