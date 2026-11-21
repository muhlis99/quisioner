@extends('layouts.admin')

@section('title', 'Detail Ketuntasan & Nilai Mahasiswa - Admin')
@section('page-title', 'Detail Rekap Mahasiswa')
@section('page-subtitle', $mahasiswa->nama . ' (' . $mahasiswa->nim . ')')

@section('header-actions')
    <button
        type="button"
        data-print-detail
        class="inline-flex items-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
        </svg>
        <span>Cetak Detail</span>
    </button>
    <a href="{{ route('admin.ketuntasan.index', array_filter(['tahun_ajaran' => $tahunAjaran, 'semester' => $semester, 'id_quisioner' => $quisionerId])) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50">
        &larr; Kembali ke Monitoring
    </a>
@endsection

@section('content')
    <div id="admin-ketuntasan-detail-root" class="space-y-6">
        {{-- Kop Dokumen Khusus Mode Cetak / PDF --}}
        <div class="hidden items-center justify-between border-b-2 border-[#062419] pb-4 print:flex">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/img/logo-stainaa.png') }}" alt="Logo STAINAA" class="h-14 w-14 object-contain">
                <div>
                    <p class="text-base font-extrabold text-[#062419]">STAI Nurul Abror Al-Robbaniyin</p>
                    <p class="text-xs font-semibold text-[#0B6B3A]">Bukti Rincian Ketuntasan &amp; Jawaban Kuisioner Mahasiswa</p>
                    <p class="text-[11px] text-slate-500">Alasbuluh, Wongsorejo, Banyuwangi</p>
                </div>
            </div>
            <div class="text-right text-xs text-slate-600">
                <p class="font-bold text-[#062419]">{{ $mahasiswa->nama }}</p>
                <p class="font-mono">NIM: {{ $mahasiswa->nim }}</p>
                <p class="text-[11px] text-slate-400">Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Banner Identitas & Filter Aktif --}}
        <div class="flex flex-col justify-between gap-4 rounded-xl bg-gradient-to-r from-[#062419] via-[#0B6B3A] to-[#20A464] p-6 text-white shadow-sm sm:flex-row sm:items-center print:hidden">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-0.5 font-mono text-xs font-semibold text-emerald-100">
                    NIM: {{ $mahasiswa->nim }}
                </span>
                <h2 class="mt-2 text-xl font-extrabold tracking-tight">{{ $mahasiswa->nama }}</h2>
                <p class="mt-1 text-xs text-emerald-100">
                    @if ($tahunAjaran !== '' || $semester !== '' || $quisionerId !== '')
                        Menampilkan data terfilter
                        @if ($tahunAjaran !== '') &middot; TA {{ $tahunAjaran }} @endif
                        @if ($semester !== '') &middot; Semester {{ ucfirst($semester) }} @endif
                    @else
                        Menampilkan rekapitulasi seluruh quisioner aktif
                    @endif
                </p>
            </div>
            @if ($tahunAjaran !== '' || $semester !== '' || $quisionerId !== '')
                <a href="{{ route('admin.ketuntasan.show', $mahasiswa->nim) }}" class="inline-flex shrink-0 items-center rounded-xl bg-white/15 px-4 py-2 text-xs font-semibold text-white ring-1 ring-white/25 transition hover:bg-white/25">
                    Lihat Semua Periode
                </a>
            @endif
        </div>

        {{-- 3 Kartu Ringkasan Mahasiswa (Nilai Akhir 0–100 & Nilai Keseluruhan / Total Skor Digabung Dalam 1 Kartu) --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 print-avoid-break">
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Ketuntasan</p>
                <p class="mt-2 text-2xl font-extrabold {{ $totalWajib > 0 && $jumlahTuntas >= $totalWajib ? 'text-[#20A464]' : 'text-amber-600' }} tabular-nums">
                    {{ $jumlahTuntas }} / {{ $totalWajib }} Tuntas
                </p>
                <p class="mt-1.5 text-xs text-slate-500">
                    {{ $totalWajib > 0 && $jumlahTuntas >= $totalWajib ? 'Seluruh tanggungan quisioner tuntas' : 'Masih ada quisioner belum tuntas' }}
                </p>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-Rata Skor</p>
                <p class="mt-2 text-3xl font-extrabold text-[#062419] tabular-nums">
                    {{ $totalJawabanKeseluruhan > 0 ? number_format($rataRataKeseluruhan, 2) : '-' }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">
                    Skor maksimal acuan: <strong class="text-slate-700">{{ rtrim(rtrim(number_format($maxSkor, 2, '.', ''), '0'), '.') }}</strong>
                </p>
            </div>

            {{-- Kartu Ringkasan Nilai Terpadu: Nilai Skala 0–100 + Badge Kelayakan + Total Skor & Jumlah Soal --}}
            <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-white via-white to-emerald-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0B6B3A]">Nilai Akhir (0–100) &amp; Nilai Keseluruhan</p>
                <div class="mt-2 flex flex-wrap items-center gap-2.5">
                    <p class="text-3xl font-extrabold tabular-nums {{ $kelayakanKeseluruhan['value_class'] }}">
                        {{ $nilaiAkhirKeseluruhan !== null ? $nilaiAkhirKeseluruhan : '-' }}
                    </p>
                    <span class="inline-flex rounded-full px-3 py-0.5 text-xs font-bold {{ $kelayakanKeseluruhan['badge_class'] }}">
                        {{ $kelayakanKeseluruhan['label'] }}
                    </span>
                </div>
                <p class="mt-2 text-xs text-slate-600">
                    @if ($totalJawabanKeseluruhan > 0)
                        Total Skor: <strong class="font-bold text-[#062419] tabular-nums">{{ number_format($totalSkorKeseluruhan) }}</strong>
                        dari <strong class="font-semibold text-slate-800 tabular-nums">{{ number_format($totalJawabanKeseluruhan) }}</strong> soal
                        <span class="text-slate-400">&middot;</span>
                        <span>Ambang &ge; 80 Sesuai</span>
                    @else
                        Belum ada jawaban pilihan ganda yang dinilai
                    @endif
                </p>
            </div>
        </div>

        {{-- 1. Tabel Rekap Ketuntasan & Nilai Per-Quisioner --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm print-avoid-break">
            <div class="border-b border-emerald-100 px-6 py-4">
                <h3 class="text-sm font-bold text-[#062419]">Rekap Ketuntasan &amp; Nilai Per-Quisioner</h3>
                <p class="mt-0.5 text-xs text-slate-500">Rincian status ketuntasan, rata-rata skor, nilai skala 0–100 beserta total skor, dan keterangan kelayakan per quisioner.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-12 px-5 py-3.5">No</th>
                            <th class="px-5 py-3.5">Quisioner</th>
                            <th class="w-36 px-5 py-3.5">Periode</th>
                            <th class="w-32 px-5 py-3.5 text-center">Status</th>
                            <th class="w-24 px-5 py-3.5 text-right">Rata-Rata</th>
                            <th class="w-44 px-5 py-3.5 text-right">Nilai (0–100) &amp; Total Skor</th>
                            <th class="w-32 px-5 py-3.5 text-center">Keterangan</th>
                            <th class="w-48 px-5 py-3.5 text-right print:hidden">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($detailQuisioner as $idx => $item)
                            @php
                                $q = $item['quisioner'];
                                $kt = $item['ketuntasan'];
                                $hasAnswers = ($item['total_entri_jawaban'] ?? 0) > 0;
                            @endphp
                            <tr class="transition hover:bg-emerald-50/40">
                                <td class="px-5 py-3.5 text-xs text-slate-500 tabular-nums">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-[#062419]">{{ $q->judul_quisioner }}</p>
                                    <span class="mt-0.5 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $q->type === 'edom' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' : 'bg-emerald-50 text-[#0B6B3A] ring-1 ring-emerald-200' }}">
                                        {{ $q->type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    <span class="font-semibold text-slate-800">{{ $q->tahun_ajaran }}</span>
                                    <span class="block capitalize text-slate-500">Semester {{ $q->semester }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($item['is_tuntas'])
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/80">
                                            Tuntas
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200/80">
                                            Belum Tuntas
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if ($item['jumlah_jawaban'] > 0)
                                        <span class="font-mono text-xs font-bold text-slate-700 tabular-nums">{{ number_format($item['rata_rata'], 2) }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if ($item['nilai_akhir'] !== null)
                                        <span class="text-base font-extrabold tabular-nums {{ $item['kelayakan']['value_class'] }}">
                                            {{ $item['nilai_akhir'] }}
                                        </span>
                                        <span class="block text-[11px] text-slate-500 tabular-nums">
                                            Total Skor: {{ number_format($item['total_skor']) }} ({{ number_format($item['jumlah_jawaban']) }} soal)
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $item['kelayakan']['badge_class'] }}">
                                        {{ $item['kelayakan']['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right print:hidden">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @if ($hasAnswers)
                                            <button
                                                type="button"
                                                data-jump-quisioner="{{ $q->id }}"
                                                class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-[#0B6B3A] transition hover:bg-[#20A464] hover:text-white"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>Lihat Jawaban</span>
                                            </button>
                                        @endif

                                        @if ($kt)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.ketuntasan.destroy', $kt) }}"
                                                data-confirm-delete
                                                data-confirm-title="Hapus Status Ketuntasan?"
                                                data-confirm-text="Catatan ketuntasan {{ $mahasiswa->nama }} pada quisioner '{{ $q->judul_quisioner }}' akan dihapus."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                    Hapus
                                                </button>
                                            </form>
                                        @elseif (! $hasAnswers)
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Belum ada quisioner aktif pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2 & 3. Section Rincian Jawaban & Skor Per-Quisioner (Tanpa Batas Entri + Ringkasan Kategori + Filter Cepat + Cetak) --}}
        <section id="rincian-jawaban-section" class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            {{-- Header Section + Tombol Cetak Detail --}}
            <div class="flex flex-col justify-between gap-3 border-b border-emerald-100 px-6 py-4 sm:flex-row sm:items-center">
                <div>
                    <h3 class="text-sm font-bold text-[#062419]">
                        Rincian Jawaban &amp; Skor Mahasiswa
                        <span class="ml-1.5 inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-[#0B6B3A] ring-1 ring-emerald-200/80 tabular-nums">
                            {{ number_format($rincianJawaban->count()) }} total jawaban
                        </span>
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Telusuri seluruh jawaban pilihan ganda dan esai milik {{ $mahasiswa->nama }} dikelompokkan per quisioner &amp; kategori.
                    </p>
                </div>

                <button
                    type="button"
                    data-print-detail
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-[#0B6B3A] transition hover:bg-[#20A464] hover:text-white print:hidden"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                    </svg>
                    <span>Cetak Detail</span>
                </button>
            </div>

            @if ($rincianJawaban->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-[#062419]">Belum Ada Jawaban Tersimpan</p>
                    <p class="mt-1 text-xs text-slate-500">Mahasiswa ini belum mengirimkan jawaban untuk quisioner pada periode yang dipilih.</p>
                </div>
            @else
                {{-- Tab Pengelompokan Per-Quisioner --}}
                <div class="border-b border-emerald-100 bg-emerald-50/35 px-6 py-3.5 print:hidden">
                    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Tab quisioner rincian jawaban">
                        @foreach ($jawabanTabs as $tabIndex => $tab)
                            <button
                                type="button"
                                data-ans-tab="{{ $tab['key'] }}"
                                data-ans-categories='@json($tab['kategori_list'])'
                                aria-pressed="{{ $tabIndex === 0 ? 'true' : 'false' }}"
                                class="ans-quisioner-tab inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $tabIndex === 0 ? 'bg-[#062419] text-white shadow-2xs' : 'border border-emerald-200 bg-white text-[#062419] hover:bg-emerald-50' }}"
                            >
                                <span>
                                    {{ $tab['label'] }}@if ($tab['type']) ({{ $tab['type'] }})@endif
                                </span>
                                <span class="ans-tab-count rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums {{ $tabIndex === 0 ? 'bg-white/20 text-white' : 'bg-emerald-50 text-[#0B6B3A]' }}">
                                    {{ number_format($tab['count']) }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Ringkasan Skor Per-Kategori (Otomatis Mengikuti Tab Aktif) --}}
                <div class="border-b border-emerald-100 bg-[#F4F7F5]/70 px-6 py-3.5">
                    @foreach ($jawabanTabs as $tabIndex => $tab)
                        <div
                            data-summary-tab="{{ $tab['key'] }}"
                            class="ans-kategori-summary-group {{ $tabIndex === 0 ? '' : 'hidden' }}"
                        >
                            @if (! empty($tab['kategori_summary']))
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="mr-1 text-[11px] font-bold uppercase tracking-wider text-emerald-900/70">Ringkasan Kategori:</span>
                                    @foreach ($tab['kategori_summary'] as $katSum)
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $katSum['kelayakan']['badge_class'] }}">
                                            <span>{{ $katSum['kategori'] }}:</span>
                                            <strong class="font-extrabold tabular-nums">{{ $katSum['nilai_100'] }}</strong>
                                            <span class="text-[10px] opacity-75">({{ number_format($katSum['rata_skor'], 2) }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-500">Belum ada soal pilihan ganda yang dinilai pada kelompok ini.</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Filter Cepat Client-Side: Kategori & Tipe Soal --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-emerald-100 bg-white px-6 py-3 print:hidden">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="flex items-center gap-2">
                            <label for="ans-filter-kategori" class="text-xs font-semibold text-slate-600">Kategori:</label>
                            <select
                                id="ans-filter-kategori"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                            >
                                <option value="">Semua Kategori</option>
                                @foreach ($jawabanTabs[0]['kategori_list'] as $katOpt)
                                    <option value="{{ $katOpt }}">{{ $katOpt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5" role="group" aria-label="Filter tipe soal">
                            <button type="button" data-ans-tipe="" class="ans-tipe-btn rounded-full bg-[#20A464] px-3 py-1 text-xs font-semibold text-white transition">
                                Semua
                            </button>
                            <button type="button" data-ans-tipe="pilihan" class="ans-tipe-btn rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50">
                                Pilihan Ganda
                            </button>
                            <button type="button" data-ans-tipe="esai" class="ans-tipe-btn rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50">
                                Esai / Isian
                            </button>
                        </div>
                    </div>

                    <span id="ans-visible-counter" class="text-xs font-semibold text-slate-500 tabular-nums">
                        Menampilkan {{ number_format($rincianJawaban->count()) }} dari {{ number_format($rincianJawaban->count()) }} jawaban
                    </span>
                </div>

                {{-- Tabel Rincian Jawaban Lengkap --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                        <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                            <tr>
                                <th class="w-40 px-5 py-3.5 whitespace-nowrap">No &amp; Tanggal</th>
                                <th class="px-5 py-3.5">Kategori &amp; Pertanyaan</th>
                                <th class="w-56 px-5 py-3.5">Konteks Evaluasi</th>
                                <th class="w-72 px-5 py-3.5">Detail Jawaban Mahasiswa</th>
                            </tr>
                        </thead>
                        <tbody id="ans-table-body" class="divide-y divide-slate-100 bg-white">
                            @foreach ($rincianJawaban as $idx => $ans)
                                @php
                                    $isEdomAns = strtolower((string) $ans->tipe_quisioner) === 'edom';
                                @endphp
                                <tr
                                    class="ans-row align-top transition hover:bg-emerald-50/35"
                                    data-quisioner-id="{{ $ans->id_quisioner }}"
                                    data-kategori="{{ $ans->kategori_clean }}"
                                    data-tipe="{{ $ans->tipe_key }}"
                                >
                                    {{-- No & Tanggal (1 Baris Rapi, Tanpa Wrap) --}}
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="ans-row-no inline-flex h-6 min-w-[24px] items-center justify-center rounded-md bg-emerald-50 px-1.5 font-mono text-[11px] font-bold text-[#0B6B3A] tabular-nums">
                                                {{ $idx + 1 }}
                                            </span>
                                            <span class="font-mono text-xs text-slate-600">{{ $ans->tanggal_display }}</span>
                                        </div>
                                    </td>

                                    {{-- Kategori & Teks Pertanyaan Lengkap + Badge Tipe Soal --}}
                                    <td class="px-5 py-3.5">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-[#0B6B3A] ring-1 ring-emerald-200/80">
                                                {{ $ans->kategori_clean }}
                                            </span>
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $ans->is_pilihan ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                                                {{ $ans->tipe_label }}
                                            </span>
                                            <span class="text-[11px] font-medium text-slate-400">&middot; {{ $ans->judul_quisioner }}</span>
                                        </div>
                                        <p class="mt-1.5 text-xs leading-relaxed font-medium text-slate-800 sm:text-sm">
                                            {{ $ans->isi_pertanyaan }}
                                        </p>
                                    </td>

                                    {{-- Konteks Evaluasi (Khusus EDOM: Nama Dosen + NIPY + Nama Mata Kuliah) --}}
                                    <td class="px-5 py-3.5">
                                        @if ($isEdomAns && ($ans->kode_makul_clean !== '' || $ans->nipy_clean !== ''))
                                            <p class="text-xs font-bold text-[#062419]">
                                                {{ $ans->nama_dosen !== '' ? $ans->nama_dosen : 'Dosen (' . $ans->nipy_clean . ')' }}
                                            </p>
                                            @if ($ans->nipy_clean !== '')
                                                <p class="font-mono text-[11px] text-slate-400">NIPY: {{ $ans->nipy_clean }}</p>
                                            @endif
                                            @if ($ans->nama_makul !== '' || $ans->kode_makul_clean !== '')
                                                <p class="mt-1 text-xs font-medium text-[#0B6B3A]">
                                                    {{ $ans->nama_makul !== '' ? $ans->nama_makul : $ans->kode_makul_clean }}
                                                    @if ($ans->nama_makul !== '' && $ans->kode_makul_clean !== '')
                                                        <span class="font-mono text-[11px] text-slate-400">({{ $ans->kode_makul_clean }})</span>
                                                    @endif
                                                </p>
                                            @endif
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                                                Umum
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Detail Jawaban Mahasiswa --}}
                                    <td class="px-5 py-3.5">
                                        @if ($ans->is_pilihan)
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 font-mono text-xs font-extrabold tabular-nums {{ $ans->kelayakan_skor['badge_class'] }}">
                                                    <span>Skor {{ $ans->skor }}</span>
                                                    @if ($ans->huruf_skor !== '')
                                                        <span>({{ $ans->huruf_skor }})</span>
                                                    @endif
                                                </span>
                                                @if ($ans->nilai_100 !== null)
                                                    <span class="text-[11px] font-bold tabular-nums {{ $ans->kelayakan_skor['value_class'] }}">
                                                        Nilai: {{ $ans->nilai_100 }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($ans->keterangan_skor !== '')
                                                <p class="mt-1 text-xs font-medium text-slate-600">{{ $ans->keterangan_skor }}</p>
                                            @endif
                                        @else
                                            <blockquote class="rounded-r-xl border-l-4 border-[#20A464] bg-[#F4F7F5] px-3.5 py-2.5 text-xs leading-relaxed text-slate-700">
                                                {{ $ans->isi_jawaban ?: '-' }}
                                            </blockquote>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="ans-empty-filter-row" class="hidden">
                                <td colspan="4" class="px-6 py-10 text-center text-xs font-medium text-slate-500">
                                    Tidak ada jawaban yang cocok dengan filter kategori/tipe soal yang dipilih.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
