@extends('layouts.admin')

@section('title', 'Jatah Mata Kuliah - Admin')
@section('page-title', 'Jatah Mata Kuliah')
@section('page-subtitle', 'Pemetaan mata kuliah dan dosen pengampu per mahasiswa untuk kuisioner EDOM (tb_jatah_makul)')

@section('header-actions')
    <a href="{{ route('admin.jatah-makul.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Jatah Makul</span>
    </a>
@endsection

@section('content')
    <div class="space-y-5">
        {{-- Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('admin.jatah-makul.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-5">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari NIM, nama mahasiswa, kode/nama makul, atau dosen..."
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    >
                </div>
                <div class="md:col-span-2">
                    <select name="tahun_ajaran" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua TA</option>
                        @foreach ($tahunAjaranList as $ta)
                            <option value="{{ $ta }}" @selected($tahunAjaran === $ta)>{{ $ta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="semester" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected($semester === 'genap')>Genap</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 md:col-span-3 md:justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Filter
                    </button>
                    @if ($search !== '' || $semester !== '' || $tahunAjaran !== '')
                        <a href="{{ route('admin.jatah-makul.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Jatah Mata Kuliah --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-12 px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Mahasiswa</th>
                            <th class="w-36 px-4 py-3.5">Periode</th>
                            <th class="px-4 py-3.5">Mata Kuliah</th>
                            <th class="px-4 py-3.5">Dosen Pengampu</th>
                            <th class="w-40 px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($jatahMakul as $index => $item)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 text-slate-500">{{ $jatahMakul->firstItem() + $index }}</td>
                                <td class="px-4 py-3.5">
                                    <p class="font-mono text-xs font-semibold text-indigo-700">{{ $item->nim }}</p>
                                    <p class="text-xs text-slate-600">{{ $item->mahasiswa->nama ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-slate-800">{{ $item->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">{{ $item->semester }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700">{{ $item->kode_makul }}</span>
                                    <p class="mt-1 font-medium text-slate-900">{{ $item->nama_makul }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-slate-800">{{ $item->nama_dosen }}</p>
                                    <p class="font-mono text-[11px] text-slate-400">NIPY: {{ $item->nipy }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.jatah-makul.edit', $item) }}" class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            Edit
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.jatah-makul.destroy', $item) }}"
                                            data-confirm-delete
                                            data-confirm-title="Hapus Jatah Mata Kuliah?"
                                            data-confirm-text="Data {{ $item->nama_makul }} untuk NIM {{ $item->nim }} akan dihapus permanen."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data jatah mata kuliah pada tabel <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">tb_jatah_makul</code>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jatahMakul->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $jatahMakul->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
