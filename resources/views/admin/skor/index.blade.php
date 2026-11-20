@extends('layouts.admin')

@section('title', 'Skor Jawaban - Admin')
@section('page-title', 'Skor Jawaban')
@section('page-subtitle', 'Pengaturan skala skor, keterangan, dan predikat huruf (tb_skor)')

@section('header-actions')
    <a href="{{ route('admin.skor.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Skor Jawaban</span>
    </a>
@endsection

@section('content')
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="w-16 px-5 py-3.5">No</th>
                        <th class="w-28 px-5 py-3.5 text-center">Skor</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="w-36 px-5 py-3.5 text-center">Huruf / Predikat</th>
                        <th class="w-44 px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($skors as $index => $skor)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 text-slate-500">{{ $skors->firstItem() + $index }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-700">
                                    {{ $skor->skor }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $skor->keterangan }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $skor->huruf }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.skor.edit', $skor) }}" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        Edit
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
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                Belum ada pengaturan skor jawaban. Klik <strong>Tambah Skor Jawaban</strong> untuk mulai menambahkan skala penilaian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($skors->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                {{ $skors->links() }}
            </div>
        @endif
    </div>
@endsection
