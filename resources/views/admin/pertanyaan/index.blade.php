@extends('layouts.admin')

@section('title', 'Pertanyaan Quisioner - Admin')
@section('page-title', 'Isi Data Pertanyaan')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('header-actions')
    <a href="{{ route('admin.quisioner.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        &larr; Kembali ke Quisioner
    </a>
    <a href="{{ route('admin.quisioner.pertanyaan.create', $quisioner) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Pertanyaan</span>
    </a>
@endsection

@section('content')
    <div class="space-y-5">
        {{-- Ringkasan Info Quisioner Terpilih --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-5 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Quisioner Terpilih</p>
                <h2 class="mt-0.5 text-base font-bold text-slate-900">{{ $quisioner->judul_quisioner }}</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="rounded-full bg-white px-3 py-1 text-slate-700 ring-1 ring-slate-200">
                    TA {{ $quisioner->tahun_ajaran }}
                </span>
                <span class="rounded-full bg-white px-3 py-1 capitalize text-slate-700 ring-1 ring-slate-200">
                    Semester {{ $quisioner->semester }}
                </span>
                <span class="rounded-full bg-indigo-600 px-3 py-1 uppercase text-white">
                    {{ $quisioner->type }}
                </span>
            </div>
        </div>

        {{-- Tabel Pertanyaan --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-14 px-5 py-3.5">No</th>
                            <th class="w-48 px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Isi Pertanyaan</th>
                            <th class="w-36 px-5 py-3.5 text-center">Tipe</th>
                            <th class="w-44 px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($pertanyaans as $index => $item)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-slate-500">{{ $pertanyaans->firstItem() + $index }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ $item->kategori }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-800">{{ $item->isi_pertanyaan }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($item->type === 'ganda')
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                                            Pilihan Ganda
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-semibold text-teal-700 ring-1 ring-teal-200">
                                            Isian / Esai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.quisioner.pertanyaan.edit', [$quisioner, $item]) }}" class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            Edit
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
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada pertanyaan untuk quisioner ini. Klik <strong>Tambah Pertanyaan</strong> untuk menambahkan butir soal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pertanyaans->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $pertanyaans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
