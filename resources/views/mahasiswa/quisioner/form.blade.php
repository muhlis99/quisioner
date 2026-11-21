@extends('layouts.mahasiswa')

@section('title', 'Isi Quisioner - Portal Mahasiswa')
@section('page-title', 'Form Pengisian Quisioner')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('header-actions')
    <a href="{{ route('mahasiswa.quisioner.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-[#0F294A] shadow-2xs transition hover:bg-blue-50 sm:text-sm">
        &larr; Kembali ke Daftar Quisioner
    </a>
@endsection

@section('content')
    <div class="mx-auto max-w-4xl space-y-5">
        {{-- Sticky Progress Tracker Real-Time --}}
        <div
            id="mhs-form-sticky-tracker"
            data-total-questions="{{ $totalPertanyaan }}"
            class="sticky top-16 z-20 rounded-2xl border border-blue-200/80 bg-white/95 px-4 py-3.5 shadow-md backdrop-blur-sm sm:px-5"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p id="mhs-form-progress-text" class="text-xs font-bold text-[#0F294A] tabular-nums sm:text-sm">
                        0 dari {{ $totalPertanyaan }} Pertanyaan Terjawab &mdash; 0%
                    </p>
                </div>
                <span id="mhs-form-remaining-badge" class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 tabular-nums">
                    Tersisa {{ $totalPertanyaan }} soal
                </span>
            </div>
            <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-blue-100">
                <div
                    id="mhs-form-progress-bar"
                    class="h-full rounded-full bg-gradient-to-r from-[#2563EB] to-[#60A5FA] transition-all duration-300 ease-out"
                    style="width: 0%"
                ></div>
            </div>
        </div>

        {{-- Ringkasan Identitas Quisioner & Target Evaluasi --}}
        <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xs">
            <div class="bg-gradient-to-r from-[#0F294A] via-[#1E3A8A] to-[#2563EB] px-5 py-4 text-white sm:px-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white/15 px-3 py-0.5 text-[11px] font-bold uppercase text-white ring-1 ring-white/25">
                        {{ $quisioner->type === 'edom' ? 'EDOM' : 'Kuisioner Umum' }}
                    </span>
                    <span class="rounded-full bg-[#0F294A]/50 px-3 py-0.5 text-[11px] font-semibold capitalize text-blue-100">
                        TA {{ $quisioner->tahun_ajaran }} &middot; Semester {{ $quisioner->semester }}
                    </span>
                    <span class="rounded-full bg-white/10 px-3 py-0.5 text-[11px] font-semibold text-blue-100">
                        {{ $totalPertanyaan }} Pertanyaan
                    </span>
                </div>
                <h2 class="mt-2 text-lg font-extrabold tracking-tight sm:text-xl">{{ $quisioner->judul_quisioner }}</h2>
            </div>

            <div class="space-y-4 p-5 sm:px-6">
                @if ($jatahMakul)
                    <div class="grid grid-cols-1 gap-3 rounded-xl border border-blue-100 bg-[#F4F7FB] p-4 text-xs sm:grid-cols-2">
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Mata Kuliah</span>
                            <p class="mt-0.5 text-sm font-bold text-[#0F294A]">{{ $jatahMakul->kode_makul }} &mdash; {{ $jatahMakul->nama_makul }}</p>
                        </div>
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Dosen Pengampu</span>
                            <p class="mt-0.5 text-sm font-bold text-[#0F294A]">
                                {{ $jatahMakul->nama_dosen }}
                                <span class="font-mono text-xs font-normal text-slate-500">({{ $jatahMakul->nipy }})</span>
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Keterangan Skala Penilaian --}}
                @if ($skors->isNotEmpty())
                    <div>
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Panduan Skala Penilaian</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($skors as $s)
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-100 bg-blue-50/50 px-2.5 py-1 text-xs text-slate-700">
                                    <strong class="font-bold text-[#2563EB]">{{ $s->skor }}</strong>
                                    <span class="font-semibold text-[#0F294A]">({{ $s->huruf }})</span>
                                    <span class="text-slate-600">&ndash; {{ $s->keterangan }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Form Pertanyaan Interaktif --}}
        <form
            id="mahasiswa-quisioner-form"
            method="POST"
            action="{{ route('mahasiswa.quisioner.store', $quisioner) }}"
            data-quisioner-title="{{ $quisioner->judul_quisioner }}"
            novalidate
            class="space-y-6"
        >
            @csrf

            @if ($jatahMakul)
                <input type="hidden" name="jatah_makul_id" value="{{ $jatahMakul->id }}">
            @endif

            @php $nomorSoal = 1; @endphp

            @foreach ($pertanyaanPerKategori as $kategori => $daftarPertanyaan)
                <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xs">
                    <div class="flex items-center justify-between border-b border-blue-100 bg-blue-50/70 px-5 py-3.5 sm:px-6">
                        <h3 class="text-sm font-bold text-[#0F294A]">{{ $kategori }}</h3>
                        <span class="rounded-full bg-white px-2.5 py-0.5 text-[11px] font-semibold text-[#1E3A8A] ring-1 ring-blue-100">
                            {{ $daftarPertanyaan->count() }} soal
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($daftarPertanyaan as $p)
                            @php
                                $currentNo = $nomorSoal++;
                                $fieldName = "jawaban[{$p->id}]";
                                $errorKey = "jawaban.{$p->id}";
                                $oldVal = old($errorKey);
                                $isPilihan = in_array(strtolower($p->type), ['ganda', 'pilihan'], true);
                            @endphp

                            <div
                                class="mhs-question-card p-5 transition sm:p-6 @error($errorKey) bg-rose-50/40 @enderror"
                                data-question-id="{{ $p->id }}"
                                data-question-no="{{ $currentNo }}"
                                data-question-type="{{ $isPilihan ? 'pilihan' : 'esai' }}"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="mhs-question-number flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-xs font-extrabold text-[#2563EB] ring-1 ring-blue-200/80 tabular-nums">
                                        {{ $currentNo }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <p class="text-sm font-semibold leading-relaxed text-[#0F294A] sm:text-[15px]">
                                                {{ $p->isi_pertanyaan }}
                                                <span class="text-rose-500">*</span>
                                            </p>
                                            <span class="mhs-unanswered-badge hidden shrink-0 items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-bold text-rose-700">
                                                Wajib diisi
                                            </span>
                                        </div>

                                        @if ($isPilihan)
                                            @if ($skors->isEmpty())
                                                <p class="mt-3 rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                                                    Opsi skor belum diatur oleh Admin pada tabel <code class="font-semibold">tb_skor</code>.
                                                </p>
                                            @else
                                                @php
                                                    $gridColsClass = match (min(max($skors->count(), 2), 5)) {
                                                        2 => 'sm:grid-cols-2',
                                                        3 => 'sm:grid-cols-3',
                                                        4 => 'sm:grid-cols-2 lg:grid-cols-4',
                                                        default => 'sm:grid-cols-2 lg:grid-cols-5',
                                                    };
                                                @endphp
                                                {{-- Interactive Radio Cards / Pills Ramah Sentuhan HP --}}
                                                <div class="mt-4 grid grid-cols-1 gap-2.5 {{ $gridColsClass }}">
                                                    @foreach ($skors as $s)
                                                        <label class="group relative flex min-h-[60px] cursor-pointer items-center justify-between gap-3 rounded-xl border-2 border-slate-200 bg-white px-4 py-3 transition duration-150 hover:border-[#3B82F6] hover:bg-blue-50/50 has-[:checked]:border-[#2563EB] has-[:checked]:bg-[#2563EB] has-[:checked]:text-white has-[:checked]:shadow-md">
                                                            <input
                                                                type="radio"
                                                                name="{{ $fieldName }}"
                                                                value="{{ $s->skor }}"
                                                                @checked((string) $oldVal === (string) $s->skor)
                                                                required
                                                                class="sr-only mhs-answer-radio"
                                                            >
                                                            <div class="min-w-0">
                                                                <span class="flex items-center gap-1.5 text-sm font-extrabold text-[#0F294A] group-has-[:checked]:text-white">
                                                                    <span>{{ $s->skor }}</span>
                                                                    <span class="rounded-md bg-blue-50 px-1.5 py-0.5 text-[11px] font-bold text-[#2563EB] group-has-[:checked]:bg-white/20 group-has-[:checked]:text-white">
                                                                        {{ $s->huruf }}
                                                                    </span>
                                                                </span>
                                                                <span class="mt-0.5 block text-xs text-slate-500 group-has-[:checked]:text-blue-100">
                                                                    {{ $s->keterangan }}
                                                                </span>
                                                            </div>
                                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-slate-300 bg-white transition group-has-[:checked]:border-white group-has-[:checked]:bg-white">
                                                                <svg class="hidden h-3 w-3 text-[#2563EB] group-has-[:checked]:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                                </svg>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            {{-- Textarea Modern dengan Focus Ring Biru & Penghitung Karakter --}}
                                            <div class="mt-3.5">
                                                <textarea
                                                    name="{{ $fieldName }}"
                                                    rows="3"
                                                    maxlength="2000"
                                                    required
                                                    placeholder="Tuliskan jawaban, kesan, atau saran Anda di sini..."
                                                    class="mhs-answer-textarea block w-full rounded-xl border-2 @error($errorKey) border-rose-400 @else border-slate-200 @enderror bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#2563EB] focus:ring-4 focus:ring-[#2563EB]/15 focus:outline-none"
                                                >{{ $oldVal }}</textarea>
                                                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-400">
                                                    <span>Isi jawaban dengan jelas dan konstruktif</span>
                                                    <span class="mhs-char-count font-mono tabular-nums">{{ mb_strlen((string) $oldVal) }} / 2.000 karakter</span>
                                                </div>
                                            </div>
                                        @endif

                                        @error($errorKey)
                                            <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Footer Aksi Kirim Jawaban --}}
            <div class="flex flex-col-reverse items-stretch justify-between gap-4 rounded-2xl border border-blue-100 bg-white p-5 shadow-xs sm:flex-row sm:items-center sm:px-6">
                <p class="text-xs leading-relaxed text-slate-500">
                    Pastikan seluruh pertanyaan telah terjawab dengan tepat sebelum mengirim. Jawaban yang telah terkirim akan langsung tersimpan dan dikunci.
                </p>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('mahasiswa.quisioner.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#1D4ED8]"
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
