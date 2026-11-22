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
    @php
        // Urutkan skala huruf rapi dari A -> E (tetap mempertahankan value="{{ $s->skor }}" asli)
        $skorsOrdered = $skors
            ->sortBy(fn ($s) => [strtoupper(trim((string) $s->huruf)) ?: 'Z', -((int) $s->skor)])
            ->values();
    @endphp

    <div class="mx-auto max-w-4xl space-y-5 pb-8">
        {{-- Sticky Progress Card (Solid White agar tidak ada kebocoran warna/garis saat di-scroll) --}}
        <div
            id="mhs-form-sticky-tracker"
            data-total-questions="{{ $totalPertanyaan }}"
            class="sticky top-[4.25rem] z-20 rounded-2xl border border-blue-100/90 bg-white px-4 py-3.5 shadow-sm ring-1 ring-blue-950/5 sm:px-5"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#2563EB] ring-1 ring-blue-100">
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
            <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                    id="mhs-form-progress-bar"
                    class="h-full rounded-full bg-gradient-to-r from-[#2563EB] to-indigo-500 transition-all duration-300 ease-out"
                    style="width: 0%"
                ></div>
            </div>
        </div>

        {{-- Ringkasan Identitas Quisioner & Panduan Skala Penilaian Ringkas --}}
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

                {{-- Panduan Skala Penilaian (Hanya Huruf = Keterangan, Tanpa Angka Skor) --}}
                @if ($skorsOrdered->isNotEmpty())
                    <div>
                        <p class="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-500">Panduan Skala Penilaian</p>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($skorsOrdered as $s)
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-blue-100 bg-blue-50/50 px-3 py-1.5 text-xs">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-[#2563EB] text-[11px] font-extrabold text-white">
                                        {{ strtoupper(trim((string) $s->huruf)) }}
                                    </span>
                                    <span class="text-slate-400">=</span>
                                    <span class="font-semibold text-[#0F294A]">{{ $s->keterangan }}</span>
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
            class="space-y-5"
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
                        <span class="rounded-full bg-white px-2.5 py-0.5 text-[11px] font-semibold text-[#1E3A8A] ring-1 ring-blue-100 tabular-nums">
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
                                class="mhs-question-card border-l-4 border-l-transparent p-5 transition-all duration-200 sm:p-6 @error($errorKey) bg-rose-50/40 @enderror"
                                data-question-id="{{ $p->id }}"
                                data-question-no="{{ $currentNo }}"
                                data-question-type="{{ $isPilihan ? 'pilihan' : 'esai' }}"
                            >
                                <div class="flex items-start gap-3 sm:gap-3.5">
                                    <span class="mhs-question-number flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-xs font-extrabold text-[#2563EB] ring-1 ring-blue-200/80 transition-colors duration-200 tabular-nums sm:h-8 sm:w-8">
                                        {{ $currentNo }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <p class="text-sm font-semibold leading-relaxed text-[#0F294A] sm:text-[15px]">
                                                {{ $p->isi_pertanyaan }}
                                                <span class="text-rose-500">*</span>
                                            </p>
                                            <div class="flex shrink-0 items-center gap-1.5">
                                                <span class="mhs-answered-badge hidden items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold text-[#2563EB] ring-1 ring-blue-200/80">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                    <span>Terjawab</span>
                                                </span>
                                                <span class="mhs-unanswered-badge hidden items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-bold text-rose-700">
                                                    Wajib diisi
                                                </span>
                                            </div>
                                        </div>

                                        @if ($isPilihan)
                                            @if ($skorsOrdered->isEmpty())
                                                <p class="mt-3 rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                                                    Opsi skor belum diatur oleh Admin pada tabel <code class="font-semibold">tb_skor</code>.
                                                </p>
                                            @else
                                                @php
                                                    $letterGridClass = match ($skorsOrdered->count()) {
                                                        2 => 'grid-cols-2 max-w-xs',
                                                        3 => 'grid-cols-3 max-w-sm',
                                                        4 => 'grid-cols-4 max-w-md',
                                                        default => 'grid-cols-5',
                                                    };
                                                @endphp
                                                {{-- Interactive Letter Tiles (Hanya Huruf A, B, C, D, E; value tetap skor asli) --}}
                                                <div class="mt-4 grid gap-2.5 sm:gap-3.5 {{ $letterGridClass }}">
                                                    @foreach ($skorsOrdered as $s)
                                                        @php $hurufLabel = strtoupper(trim((string) $s->huruf)) ?: (string) $s->skor; @endphp
                                                        <label
                                                            title="{{ $hurufLabel }} = {{ $s->keterangan }}"
                                                            class="relative block cursor-pointer select-none"
                                                        >
                                                            <input
                                                                type="radio"
                                                                name="{{ $fieldName }}"
                                                                value="{{ $s->skor }}"
                                                                @checked((string) $oldVal === (string) $s->skor)
                                                                required
                                                                aria-label="Pilihan {{ $hurufLabel }} ({{ $s->keterangan }})"
                                                                class="sr-only peer mhs-answer-radio"
                                                            >
                                                            <span class="flex h-12 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white text-lg font-bold text-slate-700 shadow-2xs transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-400 hover:bg-blue-50/70 hover:text-[#1E3A8A] peer-checked:scale-[1.03] peer-checked:border-transparent peer-checked:bg-gradient-to-br peer-checked:from-blue-600 peer-checked:to-indigo-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-blue-600/25 peer-checked:ring-2 peer-checked:ring-blue-400/40 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-500/25 sm:h-14">
                                                                {{ $hurufLabel }}
                                                            </span>
                                                            <span
                                                                aria-hidden="true"
                                                                class="pointer-events-none absolute top-1.5 right-1.5 hidden h-4 w-4 items-center justify-center rounded-full bg-white/25 text-white ring-1 ring-white/40 peer-checked:flex"
                                                            >
                                                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2">
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
                                                    class="mhs-answer-textarea block w-full rounded-2xl border @error($errorKey) border-rose-400 @else border-slate-200 @enderror bg-slate-50/50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 transition duration-200 focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-blue-500/15 focus:outline-none"
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
                <div class="flex flex-col-reverse items-stretch gap-2.5 sm:flex-row sm:items-center sm:justify-end sm:gap-3">
                    <a href="{{ route('mahasiswa.quisioner.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#1E3A8A] via-[#2563EB] to-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/30"
                    >
                        <span>Kirim Jawaban</span>
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
