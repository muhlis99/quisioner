@extends('layouts.admin')

@section('title', 'Data Quisioner - Admin')
@section('page-title', 'Data Quisioner')
@section('page-subtitle', 'Kelola daftar quisioner aktif (hapus = 0) beserta pertanyaan masing-masing')

@section('header-actions')
    <a href="{{ route('admin.quisioner.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Quisioner</span>
    </a>
@endsection

@section('content')
    <div class="space-y-5">
        {{-- Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('admin.quisioner.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-5">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari judul quisioner atau tahun ajaran..."
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    >
                </div>
                <div class="md:col-span-2">
                    <select name="semester" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="type" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Tipe</option>
                        <option value="edom" @selected($type === 'edom')>EDOM</option>
                        <option value="biasa" @selected($type === 'biasa')>Biasa</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 md:col-span-3 md:justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Filter
                    </button>
                    @if ($search !== '' || $semester !== '' || $type !== '')
                        <a href="{{ route('admin.quisioner.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Quisioner --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-14 px-5 py-3.5">No</th>
                            <th class="px-5 py-3.5">Judul Quisioner</th>
                            <th class="w-36 px-5 py-3.5">Tahun Ajaran</th>
                            <th class="w-28 px-5 py-3.5 text-center">Semester</th>
                            <th class="w-28 px-5 py-3.5 text-center">Tipe</th>
                            <th class="w-32 px-5 py-3.5 text-center">Pertanyaan</th>
                            <th class="w-64 px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($quisioners as $index => $item)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-slate-500">{{ $quisioners->firstItem() + $index }}</td>
                                <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $item->judul_quisioner }}</td>
                                <td class="px-5 py-3.5 text-slate-700">{{ $item->tahun_ajaran }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-slate-700">
                                        {{ $item->semester }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($item->type === 'edom')
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold uppercase text-indigo-700 ring-1 ring-indigo-200">
                                            EDOM
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold uppercase text-emerald-700 ring-1 ring-emerald-200">
                                            Biasa
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                        {{ $item->pertanyaan_count }} soal
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.quisioner.pertanyaan.index', $item) }}" class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                            Pertanyaan
                                        </a>
                                        <a href="{{ route('admin.quisioner.edit', $item) }}" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            Edit
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
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data quisioner aktif. Klik <strong>Tambah Quisioner</strong> untuk membuat yang baru.
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
