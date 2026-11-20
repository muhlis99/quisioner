@extends('layouts.admin')

@section('title', 'Monitoring Ketuntasan - Admin')
@section('page-title', 'Monitoring Ketuntasan')
@section('page-subtitle', 'Pantau progres dan status ketuntasan pengisian kuisioner mahasiswa (tb_ketuntasan)')

@section('content')
    <div class="space-y-6">
        {{-- Kartu Statistik Ringkas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Rekaman Ketuntasan</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalData) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Sudah Tuntas (1)</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($totalTuntas) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Belum Tuntas (0)</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($totalBelum) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Persentase Ketuntasan</p>
                <p class="mt-2 text-2xl font-bold text-indigo-600">{{ number_format($persentase, 1) }}%</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('admin.ketuntasan.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari NIM atau nama mahasiswa..."
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    >
                </div>
                <div class="md:col-span-3">
                    <select name="id_quisioner" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Quisioner</option>
                        @foreach ($quisionerList as $q)
                            <option value="{{ $q->id }}" @selected($quisionerId === (string) $q->id)>
                                {{ $q->judul_quisioner }} ({{ $q->tahun_ajaran }})
                            </option>
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
                <div class="md:col-span-2">
                    <select name="ketuntasan" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($status === '1')>Tuntas (1)</option>
                        <option value="0" @selected($status === '0')>Belum Tuntas (0)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 md:col-span-2 md:justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Filter
                    </button>
                    @if ($search !== '' || $quisionerId !== '' || $semester !== '' || $status !== '')
                        <a href="{{ route('admin.ketuntasan.index') }}" class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Monitoring Ketuntasan --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-12 px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Mahasiswa</th>
                            <th class="px-4 py-3.5">Quisioner</th>
                            <th class="w-36 px-4 py-3.5">Periode</th>
                            <th class="w-40 px-4 py-3.5 text-center">Progres</th>
                            <th class="w-32 px-4 py-3.5 text-center">Status</th>
                            <th class="w-24 px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($ketuntasans as $index => $item)
                            @php
                                $target = max(1, (int) $item->status_insert_soal);
                                $selesai = (int) $item->status_penyelesaian;
                                $pct = min(100, (int) round(($selesai / $target) * 100));
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 text-slate-500">{{ $ketuntasans->firstItem() + $index }}</td>
                                <td class="px-4 py-3.5">
                                    <p class="font-mono text-xs font-semibold text-indigo-700">{{ $item->niim }}</p>
                                    <p class="text-xs text-slate-600">{{ $item->mahasiswa->nama ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-900">
                                    {{ $item->quisioner->judul_quisioner ?? 'Quisioner #' . $item->id_quisioner }}
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-slate-800">{{ $item->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">{{ $item->semester }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full {{ $item->ketuntasan === '1' ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="font-mono text-xs text-slate-600">{{ $selesai }}/{{ $item->status_insert_soal }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if ($item->ketuntasan === '1')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                            Belum Tuntas
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.ketuntasan.destroy', $item) }}"
                                        data-confirm-delete
                                        data-confirm-title="Hapus Data Ketuntasan?"
                                        data-confirm-text="Data ketuntasan untuk NIM {{ $item->niim }} akan dihapus."
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data ketuntasan mahasiswa pada tabel <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">tb_ketuntasan</code>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ketuntasans->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $ketuntasans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
