@extends('layouts.admin')

@section('title', 'Pertanyaan Quisioner - Admin')
@section('page-title', 'Isi Data Pertanyaan')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('content')
    <div class="space-y-5">
        {{-- Ringkasan Info Quisioner Terpilih --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-emerald-200 bg-emerald-50/70 px-6 py-4 shadow-sm">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-[#0B6B3A]">Quisioner Terpilih</p>
                <h2 class="mt-0.5 text-base font-bold text-[#062419]">{{ $quisioner->judul_quisioner }}</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="rounded-full bg-white px-3 py-1 text-slate-700 ring-1 ring-emerald-200">
                    TA {{ $quisioner->tahun_ajaran }}
                </span>
                <span class="rounded-full bg-white px-3 py-1 capitalize text-slate-700 ring-1 ring-emerald-200">
                    Semester {{ $quisioner->semester }}
                </span>
                <span class="rounded-full px-3 py-1 uppercase {{ $quisioner->type === 'edom' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-300' }}">
                    {{ $quisioner->type }}
                </span>
            </div>
        </div>

        {{-- Card Tabel Pertanyaan --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            {{-- Toolbar Atas Card --}}
            <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.quisioner.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-emerald-50 hover:text-[#0B6B3A]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/70">
                        {{ $pertanyaans->total() }} pertanyaan
                    </span>
                </div>

                <a href="{{ route('admin.quisioner.pertanyaan.create', $quisioner) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Pertanyaan</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-14 px-6 py-3.5">No</th>
                            <th class="w-48 px-6 py-3.5">Kategori</th>
                            <th class="px-6 py-3.5">Isi Pertanyaan</th>
                            <th class="w-36 px-6 py-3.5 text-center">Tipe</th>
                            <th class="w-48 px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($pertanyaans as $index => $item)
                            <tr class="transition hover:bg-emerald-50/50">
                                <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $pertanyaans->firstItem() + $index }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 leading-relaxed text-slate-800">{{ $item->isi_pertanyaan }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($item->type === 'ganda')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/80">
                                            Pilihan Ganda
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200/80">
                                            Isian / Esai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.quisioner.pertanyaan.edit', [$quisioner, $item]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                            <span>Edit</span>
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('admin.quisioner.pertanyaan.destroy', [$quisioner, $item]) }}"
                                            data-confirm-delete
                                            data-confirm-title="Hapus Pertanyaan?"
                                            data-confirm-text="Pertanyaan ini akan dihapus dari quisioner terpilih."
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
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Belum ada pertanyaan untuk quisioner ini. Klik <strong>Tambah Pertanyaan</strong> untuk menambahkan butir soal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pertanyaans->hasPages())
                <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                    {{ $pertanyaans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
