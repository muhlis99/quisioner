@extends('layouts.admin')

@section('title', 'Skor Jawaban - Admin')
@section('page-title', 'Skor Jawaban')
@section('page-subtitle', 'Pengaturan bobot angka skor, keterangan, dan predikat huruf untuk penilaian kuisioner')

@section('content')
    <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        {{-- Toolbar Atas Card --}}
        <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="text-sm font-bold text-[#062419]">Daftar Skala Skor Penilaian</h2>
                <p class="mt-0.5 text-xs text-slate-500">Digunakan sebagai pilihan jawaban pada pertanyaan tipe Pilihan Ganda</p>
            </div>

            <a href="{{ route('admin.skor.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Skor Jawaban</span>
            </a>
        </div>

        {{-- Tabel Skor --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                    <tr>
                        <th class="w-16 px-6 py-3.5">No</th>
                        <th class="w-28 px-6 py-3.5 text-center">Skor</th>
                        <th class="px-6 py-3.5">Keterangan</th>
                        <th class="w-36 px-6 py-3.5 text-center">Predikat Huruf</th>
                        <th class="w-48 px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($skors as $index => $skor)
                        <tr class="transition hover:bg-emerald-50/50">
                            <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $skors->firstItem() + $index }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-[#0B6B3A] ring-1 ring-emerald-200/80 tabular-nums">
                                    {{ $skor->skor }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $skor->keterangan }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 ring-1 ring-emerald-200/80">
                                    {{ $skor->huruf }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.skor.edit', $skor) }}" class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        <span>Edit</span>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.skor.destroy', $skor) }}"
                                        data-confirm-delete
                                        data-confirm-title="Hapus Skor Jawaban?"
                                        data-confirm-text="Skor {{ $skor->skor }} ({{ $skor->keterangan }}) akan dihapus secara permanen."
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
                                Belum ada pengaturan skor jawaban. Klik <strong>Tambah Skor Jawaban</strong> untuk mulai menambahkan skala penilaian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($skors->hasPages())
            <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                {{ $skors->links() }}
            </div>
        @endif
    </div>
@endsection
