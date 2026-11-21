@extends('layouts.mahasiswa')

@section('title', 'Daftar Quisioner - Portal Mahasiswa')
@section('page-title', 'Daftar Quisioner')
@section('page-subtitle', 'Pilih kuisioner EDOM maupun kuisioner umum aktif di bawah ini untuk mulai mengisi')

@section('content')
    <div class="space-y-6">
        {{-- Filter Tab Tipe Quisioner --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-white p-3.5 shadow-xs sm:px-5">
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('mahasiswa.quisioner.index') }}"
                    class="rounded-xl px-4 py-2 text-xs font-bold transition sm:text-sm {{ $filterType === '' ? 'bg-[#2563EB] text-white shadow-sm' : 'border border-blue-100 bg-blue-50/40 text-[#0F294A] hover:bg-blue-50' }}"
                >
                    Semua Quisioner
                </a>
                <a
                    href="{{ route('mahasiswa.quisioner.index', ['type' => 'edom']) }}"
                    class="rounded-xl px-4 py-2 text-xs font-bold transition sm:text-sm {{ $filterType === 'edom' ? 'bg-[#2563EB] text-white shadow-sm' : 'border border-blue-100 bg-blue-50/40 text-[#0F294A] hover:bg-blue-50' }}"
                >
                    Kuisioner EDOM
                </a>
                <a
                    href="{{ route('mahasiswa.quisioner.index', ['type' => 'biasa']) }}"
                    class="rounded-xl px-4 py-2 text-xs font-bold transition sm:text-sm {{ $filterType === 'biasa' ? 'bg-[#2563EB] text-white shadow-sm' : 'border border-blue-100 bg-blue-50/40 text-[#0F294A] hover:bg-blue-50' }}"
                >
                    Kuisioner Umum
                </a>
            </div>
            <span class="text-xs font-semibold text-slate-500 tabular-nums">
                Total: <strong class="text-[#0F294A]">{{ $items->count() }}</strong> instrumen aktif
            </span>
        </div>

        @if ($items->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-blue-200 bg-white px-6 py-14 text-center shadow-xs">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-[#2563EB]">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25" />
                    </svg>
                </span>
                <p class="mt-4 text-sm font-bold text-[#0F294A]">Belum ada quisioner aktif saat ini</p>
                <p class="mt-1 max-w-md text-xs text-slate-500">Silakan cek kembali secara berkala saat periode pengisian EDOM atau kuisioner umum dibuka.</p>
            </div>
        @else
            @php
                $biasaItems = $items->filter(fn ($e) => $e['quisioner']->type !== 'edom')->values();
                $edomItems = $items->filter(fn ($e) => $e['quisioner']->type === 'edom')->values();
            @endphp

            {{-- Card Grid Responsif: Kuisioner Umum (Biasa) --}}
            @if ($biasaItems->isNotEmpty())
                <div class="space-y-3">
                    @if ($filterType === '')
                        <h2 class="px-1 text-xs font-bold uppercase tracking-wider text-slate-500">Kuisioner Umum</h2>
                    @endif
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach ($biasaItems as $entry)
                            @php
                                $q = $entry['quisioner'];
                                $isTuntas = $entry['is_tuntas'];
                            @endphp
                            <article class="flex flex-col justify-between rounded-2xl border p-5 transition duration-200 {{ $isTuntas ? 'border-emerald-200/80 bg-white' : 'border-blue-100 bg-white shadow-xs hover:-translate-y-0.5 hover:border-[#3B82F6]/60 hover:shadow-md' }}">
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-0.5 text-[11px] font-bold uppercase text-sky-700 ring-1 ring-sky-200">
                                                Kuisioner Umum
                                            </span>
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold capitalize text-slate-600">
                                                TA {{ $q->tahun_ajaran }} &middot; Sem. {{ $q->semester }}
                                            </span>
                                        </div>

                                        @if ($isTuntas)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">
                                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Tuntas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Belum Diisi
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="mt-3 text-base font-bold text-[#0F294A] sm:text-lg">{{ $q->judul_quisioner }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">Terdiri dari {{ number_format($q->pertanyaan_count) }} butir pertanyaan wajib.</p>
                                </div>

                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                    <span class="text-xs font-medium text-slate-500">
                                        {{ number_format($q->pertanyaan_count) }} pertanyaan
                                    </span>

                                    @if ($isTuntas)
                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            Sudah Selesai
                                        </span>
                                    @elseif ($q->pertanyaan_count > 0)
                                        <a href="{{ route('mahasiswa.quisioner.show', $q) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#1D4ED8] sm:text-sm">
                                            <span>Isi Sekarang</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    @else
                                        <span class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500">Belum ada soal</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Kartu Kuisioner EDOM + Card Grid Mata Kuliah / Dosen Responsif (Mobile-First) --}}
            @if ($edomItems->isNotEmpty())
                <div class="space-y-4">
                    @if ($filterType === '')
                        <h2 class="px-1 text-xs font-bold uppercase tracking-wider text-slate-500">Evaluasi Dosen Oleh Mahasiswa (EDOM)</h2>
                    @endif

                    @foreach ($edomItems as $entry)
                        @php
                            $q = $entry['quisioner'];
                            $isTuntas = $entry['is_tuntas'];
                            $totalMakul = (int) $entry['total_makul'];
                            $selesaiMakul = (int) $entry['selesai_makul'];
                            $edomPct = $totalMakul > 0 ? (int) round(($selesaiMakul / $totalMakul) * 100) : ($isTuntas ? 100 : 0);
                        @endphp

                        <section class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xs">
                            {{-- Header Kartu EDOM --}}
                            <div class="border-b border-blue-100 bg-gradient-to-r from-[#0F294A] via-[#1E3A8A] to-[#2563EB] p-5 text-white sm:p-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-bold uppercase text-white ring-1 ring-white/25">
                                                EDOM
                                            </span>
                                            <span class="inline-flex rounded-full bg-[#0F294A]/50 px-2.5 py-0.5 text-[11px] font-semibold capitalize text-blue-100">
                                                TA {{ $q->tahun_ajaran }} &middot; Semester {{ $q->semester }}
                                            </span>
                                            <span class="inline-flex rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-medium text-blue-100">
                                                {{ $q->pertanyaan_count }} pertanyaan
                                            </span>
                                        </div>
                                        <h3 class="text-base font-extrabold tracking-tight sm:text-lg">{{ $q->judul_quisioner }}</h3>
                                    </div>

                                    <div class="flex shrink-0 flex-col items-start gap-1.5 sm:items-end">
                                        @if ($isTuntas)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-3.5 py-1 text-xs font-bold text-white shadow-xs">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Tuntas ({{ $selesaiMakul }}/{{ $totalMakul }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/20 px-3.5 py-1 text-xs font-bold text-amber-200 ring-1 ring-amber-300/40">
                                                Progres: {{ $selesaiMakul }} / {{ $totalMakul }} Mata Kuliah ({{ $edomPct }}%)
                                            </span>
                                        @endif

                                        <div class="h-2 w-44 overflow-hidden rounded-full bg-[#0F294A]/50">
                                            <div class="h-full rounded-full {{ $isTuntas ? 'bg-emerald-400' : 'bg-[#60A5FA]' }}" style="width: {{ $edomPct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Grid Mata Kuliah & Dosen Pengampu --}}
                            <div class="bg-[#F4F7FB]/70 p-4 sm:p-6">
                                @if ($entry['makul_items']->isEmpty())
                                    <div class="rounded-xl border border-dashed border-blue-200 bg-white px-4 py-6 text-center text-xs text-slate-500">
                                        Belum ada data mata kuliah terdaftar untuk NIM Anda pada periode <strong>{{ $q->tahun_ajaran }} ({{ $q->semester }})</strong>.
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 gap-3.5 md:grid-cols-2">
                                        @foreach ($entry['makul_items'] as $mItem)
                                            @php
                                                $m = $mItem['makul'];
                                                $sudahDiisi = $mItem['sudah_diisi'];
                                            @endphp
                                            <article class="flex flex-col justify-between rounded-2xl border p-4 transition duration-200 {{ $sudahDiisi ? 'border-emerald-200/80 bg-emerald-50/25' : 'border-blue-100 bg-white shadow-2xs hover:-translate-y-0.5 hover:border-[#3B82F6]/60 hover:shadow-md' }}">
                                                <div>
                                                    <div class="flex items-start justify-between gap-2">
                                                        <span class="inline-flex rounded-lg bg-blue-50 px-2 py-0.5 font-mono text-[11px] font-bold text-[#1E3A8A] ring-1 ring-blue-100">
                                                            {{ $m->kode_makul }}
                                                        </span>

                                                        @if ($sudahDiisi)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800">
                                                                <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                                </svg>
                                                                Tuntas
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                                Belum Diisi
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <h4 class="mt-2.5 text-sm font-bold text-[#0F294A] sm:text-base">{{ $m->nama_makul }}</h4>
                                                    <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                                                        <svg class="h-3.5 w-3.5 shrink-0 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                        </svg>
                                                        <span class="font-medium text-slate-700">{{ $m->nama_dosen }}</span>
                                                    </p>
                                                </div>

                                                <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-3">
                                                    <span class="font-mono text-[11px] text-slate-400">NIPY: {{ $m->nipy }}</span>

                                                    @if ($sudahDiisi)
                                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                            </svg>
                                                            Sudah Terkirim
                                                        </span>
                                                    @elseif ($q->pertanyaan_count > 0)
                                                        <a
                                                            href="{{ route('mahasiswa.quisioner.show', [$q, 'makul' => $m->id]) }}"
                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#1D4ED8]"
                                                        >
                                                            <span>Isi Sekarang</span>
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-slate-400">Belum ada soal</span>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection
