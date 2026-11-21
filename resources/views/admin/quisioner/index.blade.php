@extends('layouts.admin')

@section('title', 'Data Quisioner - Admin')
@section('page-title', 'Data Quisioner')
@section('page-subtitle', 'Kelola instrumen kuisioner aktif beserta butir pertanyaannya')

@section('content')
    <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        {{-- Toolbar Atas Card --}}
        <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 xl:flex-row xl:items-center xl:justify-between sm:px-6">
            <form method="GET" action="{{ route('admin.quisioner.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
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
                        placeholder="Cari judul atau tahun ajaran..."
                        class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                    >
                </div>

                <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                    <option value="">Semua Semester</option>
                    <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                    <option value="genap" @selected($semester === 'genap')>Genap</option>
                </select>

                <select name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                    <option value="">Semua Tipe</option>
                    <option value="edom" @selected($type === 'edom')>EDOM</option>
                    <option value="biasa" @selected($type === 'biasa')>Biasa</option>
                </select>

                <button type="submit" class="rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                    Filter
                </button>

                @if ($search !== '' || $semester !== '' || $type !== '')
                    <a href="{{ route('admin.quisioner.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.quisioner.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Quisioner</span>
            </a>
        </div>

        {{-- Tabel Quisioner --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                    <tr>
                        <th class="w-14 px-6 py-3.5">No</th>
                        <th class="px-6 py-3.5">Judul Quisioner</th>
                        <th class="w-36 px-6 py-3.5">Tahun Ajaran</th>
                        <th class="w-28 px-6 py-3.5 text-center">Semester</th>
                        <th class="w-28 px-6 py-3.5 text-center">Tipe</th>
                        <th class="w-32 px-6 py-3.5 text-center">Pertanyaan</th>
                        <th class="w-72 px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($quisioners as $index => $item)
                        <tr class="transition hover:bg-emerald-50/50">
                            <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $quisioners->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->judul_quisioner }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $item->tahun_ajaran }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">
                                    {{ $item->semester }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->type === 'edom')
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase text-amber-700 ring-1 ring-amber-200/80">
                                        EDOM
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase text-emerald-700 ring-1 ring-emerald-200/80">
                                        Biasa
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-emerald-50/70 px-3 py-1 text-xs font-bold text-[#0B6B3A] tabular-nums">
                                    {{ $item->pertanyaan_count }} soal
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.quisioner.pertanyaan.index', $item) }}" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#0B6B3A] transition hover:bg-emerald-100">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        <span>Pertanyaan</span>
                                    </a>
                                    <a href="{{ route('admin.quisioner.edit', $item) }}" class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        <span>Edit</span>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.quisioner.destroy', $item) }}"
                                        data-confirm-delete
                                        data-confirm-title="Hapus Quisioner?"
                                        data-confirm-text="Quisioner '{{ $item->judul_quisioner }}' akan dinonaktifkan (hapus = 1) dan disembunyikan dari daftar."
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                Belum ada data quisioner aktif. Klik <strong>Tambah Quisioner</strong> untuk membuat yang baru.
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
