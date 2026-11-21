@extends('layouts.admin')

@section('title', 'Edit Skor Jawaban - Admin')
@section('page-title', 'Edit Skor Jawaban')
@section('page-subtitle', 'Perbarui nilai, keterangan, atau predikat huruf skor')

@section('content')
    <div class="mx-auto max-w-2xl overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-[#062419]">Perbarui Skor Jawaban</h2>
            <p class="mt-0.5 text-xs text-slate-500">Ubah bobot angka, keterangan, atau predikat huruf sesuai kebutuhan.</p>
        </div>

        <form method="POST" action="{{ route('admin.skor.update', $skor) }}" class="space-y-5 p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div>
                <label for="skor" class="block text-sm font-semibold text-slate-700">Nilai Skor <span class="text-rose-500">*</span></label>
                <input
                    id="skor"
                    name="skor"
                    type="number"
                    min="0"
                    max="100"
                    value="{{ old('skor', $skor->skor) }}"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                @error('skor')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-semibold text-slate-700">Keterangan <span class="text-rose-500">*</span></label>
                <input
                    id="keterangan"
                    name="keterangan"
                    type="text"
                    value="{{ old('keterangan', $skor->keterangan) }}"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                @error('keterangan')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="huruf" class="block text-sm font-semibold text-slate-700">Huruf / Predikat <span class="text-rose-500">*</span></label>
                <input
                    id="huruf"
                    name="huruf"
                    type="text"
                    value="{{ old('huruf', $skor->huruf) }}"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                @error('huruf')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-emerald-100 pt-5">
                <a href="{{ route('admin.skor.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    &larr; Kembali
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                    <span>Perbarui Skor</span>
                </button>
            </div>
        </form>
    </div>
@endsection
