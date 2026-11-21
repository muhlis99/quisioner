@extends('layouts.mahasiswa')

@section('title', 'Status Ketuntasan - Portal Mahasiswa')
@section('page-title', 'Status Ketuntasan')
@section('page-subtitle', 'Pantau status dan riwayat ketuntasan pengisian kuisioner Anda (tb_ketuntasan)')

@section('content')
    <div class="space-y-6">
        {{-- Kartu Statistik Ringkas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Quisioner Aktif</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalAktif) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Sudah Tuntas</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($totalTuntas) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Belum Tuntas</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($totalBelum) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Persentase Ketuntasan</p>
                <p class="mt-2 text-2xl font-bold text-teal-600">{{ number_format($persentase, 1) }}%</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('mahasiswa.ketuntasan.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="semester" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                    <option value="">Semua Semester</option>
                    <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                    <option value="genap" @selected($semester === 'genap')>Genap</option>
                </select>
                <select name="ketuntasan" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="1" @selected($status === '1')>Sudah Tuntas</option>
                    <option value="0" @selected($status === '0')>Belum Tuntas</option>
                </select>
                <button type="submit" class="rounded-xl bg-[#0B6B3A] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#20A464]">
                    Filter
                </button>
                @if ($semester !== '' || $status !== '')
                    <a href="{{ route('mahasiswa.ketuntasan.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Status Quisioner Aktif --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-bold text-slate-900">Status Ketuntasan Quisioner Aktif</h2>
                <p class="text-xs text-slate-500">Ringkasan kelengkapan pengisian untuk seluruh quisioner yang sedang berjalan</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-12 px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Judul Quisioner</th>
                            <th class="w-36 px-4 py-3.5">Periode</th>
                            <th class="w-24 px-4 py-3.5 text-center">Tipe</th>
                            <th class="w-48 px-4 py-3.5 text-center">Progres</th>
                            <th class="w-32 px-4 py-3.5 text-center">Status</th>
                            <th class="w-36 px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($statusAktif as $index => $row)
                            @php $q = $row['quisioner']; @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $q->judul_quisioner }}</td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-slate-800">{{ $q->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">Semester {{ $q->semester }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase {{ $q->type === 'edom' ? 'bg-[#E9F7F0] text-[#0B6B3A] ring-1 ring-[#20A464]/40' : 'bg-teal-50 text-teal-700 ring-1 ring-teal-200' }}">
                                        {{ $q->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full {{ $row['is_tuntas'] ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $row['persen'] }}%"></div>
                                        </div>
                                        <span class="text-[11px] text-slate-500">
                                            {{ $row['selesai'] }} / {{ $row['target'] }} {{ $row['satuan'] }} ({{ $row['persen'] }}%)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if ($row['is_tuntas'])
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
                                    @if ($row['is_tuntas'])
                                        <span class="text-xs font-medium text-emerald-600">Selesai</span>
                                    @else
                                        <a href="{{ route('mahasiswa.quisioner.index', ['type' => $q->type]) }}" class="inline-flex items-center gap-1 rounded-lg bg-[#0B6B3A] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#20A464]">
                                            <span>Lanjutkan</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Tidak ada data quisioner aktif yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Riwayat Rekaman tb_ketuntasan --}}
        @if ($riwayatKetuntasan->isNotEmpty())
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Catatan Ketuntasan</h2>
                    <p class="text-xs text-slate-500">Data historis yang tercatat di <code class="rounded bg-slate-100 px-1">tb_ketuntasan</code></p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                            <tr>
                                <th class="w-12 px-4 py-3">No</th>
                                <th class="px-4 py-3">Quisioner</th>
                                <th class="w-36 px-4 py-3">Periode</th>
                                <th class="w-32 px-4 py-3 text-center">Penyelesaian</th>
                                <th class="w-32 px-4 py-3 text-center">Ketuntasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($riwayatKetuntasan as $idx => $rec)
                                <tr>
                                    <td class="px-4 py-3 text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        {{ $rec->quisioner->judul_quisioner ?? 'Quisioner #' . $rec->id_quisioner }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-700">
                                        {{ $rec->tahun_ajaran }} &middot; <span class="capitalize">{{ $rec->semester }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs text-slate-700">
                                        {{ $rec->status_penyelesaian }} / {{ $rec->status_insert_soal }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($rec->ketuntasan === '1')
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Tuntas (1)</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Belum (0)</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
