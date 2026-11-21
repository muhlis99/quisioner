@extends('layouts.mahasiswa')

@section('title', 'Isi Quisioner - Portal Mahasiswa')
@section('page-title', 'Form Pengisian Quisioner')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('header-actions')
    <a href="{{ route('mahasiswa.quisioner.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        &larr; Kembali ke Daftar Quisioner
    </a>
@endsection

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Ringkasan Identitas Quisioner & Target Evaluasi --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-5 shadow-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-[#0B6B3A] px-3 py-0.5 text-xs font-semibold uppercase text-white">
                    {{ $quisioner->type === 'edom' ? 'EDOM' : 'Kuisioner Umum' }}
                </span>
                <span class="rounded-full bg-white px-3 py-0.5 text-xs font-semibold capitalize text-slate-700 ring-1 ring-slate-200">
                    TA {{ $quisioner->tahun_ajaran }} &middot; Semester {{ $quisioner->semester }}
                </span>
                <span class="rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                    Total {{ $totalPertanyaan }} Pertanyaan
                </span>
            </div>

            <h2 class="mt-2.5 text-lg font-bold text-slate-900">{{ $quisioner->judul_quisioner }}</h2>

            @if ($jatahMakul)
                <div class="mt-3 grid grid-cols-1 gap-2 rounded-xl border border-emerald-200/80 bg-white p-3.5 text-xs sm:grid-cols-2">
                    <div>
                        <span class="text-slate-400">Mata Kuliah:</span>
                        <p class="font-bold text-slate-900">{{ $jatahMakul->kode_makul }} &mdash; {{ $jatahMakul->nama_makul }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Dosen Pengampu:</span>
                        <p class="font-bold text-slate-900">{{ $jatahMakul->nama_dosen }} <span class="font-normal text-slate-500">({{ $jatahMakul->nipy }})</span></p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Keterangan Skala Penilaian --}}
        @if ($skors->isNotEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                <p class="mb-2.5 text-xs font-bold uppercase tracking-wider text-slate-500">Keterangan Skala Penilaian</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($skors as $s)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-700">
                            <strong class="text-emerald-700">{{ $s->skor }}</strong>
                            <span>({{ $s->huruf }})</span>
                            <span>&ndash; {{ $s->keterangan }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Form Pertanyaan --}}
        <form method="POST" action="{{ route('mahasiswa.quisioner.store', $quisioner) }}" class="space-y-6">
            @csrf

            @if ($jatahMakul)
                <input type="hidden" name="jatah_makul_id" value="{{ $jatahMakul->id }}">
            @endif

            @php $nomorSoal = 1; @endphp

            @foreach ($pertanyaanPerKategori as $kategori => $daftarPertanyaan)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-3.5">
                        <h3 class="text-sm font-bold text-slate-800">{{ $kategori }}</h3>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($daftarPertanyaan as $p)
                            @php
                                $fieldName = "jawaban[{$p->id}]";
                                $errorKey = "jawaban.{$p->id}";
                                $oldVal = old($errorKey);
                                $isPilihan = in_array(strtolower($p->type), ['ganda', 'pilihan'], true);
                            @endphp

                            <div class="p-5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                        {{ $nomorSoal++ }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold leading-relaxed text-slate-900">
                                            {{ $p->isi_pertanyaan }}
                                            <span class="text-rose-500">*</span>
                                        </p>

                                        @if ($isPilihan)
                                            @if ($skors->isEmpty())
                                                <p class="mt-3 rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                                                    Opsi skor belum diatur oleh Admin pada tabel <code class="font-semibold">tb_skor</code>.
                                                </p>
                                            @else
                                                @php
                                                    $gridColsClass = match (min(max($skors->count(), 2), 5)) {
                                                        2 => 'lg:grid-cols-2',
                                                        3 => 'lg:grid-cols-3',
                                                        4 => 'lg:grid-cols-4',
                                                        default => 'lg:grid-cols-5',
                                                    };
                                                @endphp
                                                <div class="mt-3.5 grid grid-cols-1 gap-2.5 sm:grid-cols-2 {{ $gridColsClass }}">
                                                    @foreach ($skors as $s)
                                                        <label class="group relative flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 transition hover:border-emerald-400 hover:bg-emerald-50/40 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-600">
                                                            <input
                                                                type="radio"
                                                                name="{{ $fieldName }}"
                                                                value="{{ $s->skor }}"
                                                                @checked((string) $oldVal === (string) $s->skor)
                                                                required
                                                                class="h-4 w-4 border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                            >
                                                            <div class="min-w-0">
                                                                <span class="block text-xs font-bold text-slate-900">{{ $s->skor }} &middot; {{ $s->huruf }}</span>
                                                                <span class="block truncate text-[11px] text-slate-500">{{ $s->keterangan }}</span>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            <div class="mt-3">
                                                <textarea
                                                    name="{{ $fieldName }}"
                                                    rows="3"
                                                    required
                                                    placeholder="Tuliskan jawaban atau masukan Anda di sini..."
                                                    class="block w-full rounded-xl border @error($errorKey) border-rose-400 @else border-slate-300 @enderror px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                                                >{{ $oldVal }}</textarea>
                                            </div>
                                        @endif

                                        @error($errorKey)
                                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Tombol Kirim --}}
            <div class="flex flex-col-reverse items-stretch justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs sm:flex-row sm:items-center">
                <p class="text-xs text-slate-500">
                    Pastikan semua jawaban sudah sesuai sebelum dikirim. Jawaban yang telah dikirim tidak dapat diubah kembali.
                </p>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('mahasiswa.quisioner.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0B6B3A] px-6 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-[#20A464]"
                    >
                        <span>Kirim Jawaban</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
