@extends('layouts.admin')

@section('title', 'Laporan Hasil Quisioner - Admin')
@section('page-title', 'Hasil Laporan Quisioner')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('content')
    <div class="space-y-6">
        {{-- Card Ringkasan Informasi Quisioner (Siap Cetak) --}}
        <div class="print-avoid-break overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            <div class="border-b border-emerald-100 bg-gradient-to-r from-[#062419] via-[#0B6B3A] to-[#20A464] px-6 py-5 text-white">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-0.5 text-xs font-semibold uppercase {{ $quisioner->type === 'edom' ? 'bg-amber-400/20 text-amber-100 ring-1 ring-amber-300/50' : 'bg-white/20 text-white ring-1 ring-white/30' }}">
                                {{ $quisioner->type === 'edom' ? 'EDOM' : 'Quisioner Biasa' }}
                            </span>
                            <span class="rounded-full bg-white/15 px-3 py-0.5 text-xs font-semibold capitalize text-emerald-50">
                                TA {{ $quisioner->tahun_ajaran }} &middot; Semester {{ $quisioner->semester }}
                            </span>
                            <span class="hidden text-xs text-emerald-100 print:inline">
                                Dicetak: {{ now()->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <h2 class="mt-2 text-lg font-bold tracking-tight sm:text-xl">{{ $quisioner->judul_quisioner }}</h2>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 print:hidden">
                        <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-3.5 py-2 text-xs font-semibold text-white ring-1 ring-white/25 transition hover:bg-white/25">
                            &larr; Kembali
                        </a>
                        <button
                            type="button"
                            onclick="window.print()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-xs font-bold text-[#062419] shadow-sm transition hover:bg-emerald-50"
                        >
                            <svg class="h-4 w-4 text-[#0B6B3A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                            </svg>
                            <span>Cetak Laporan</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Angka + Filter Dosen (khusus EDOM) --}}
            <div class="grid grid-cols-1 divide-y divide-emerald-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Responden</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-[#062419] tabular-nums">{{ number_format($totalResponden) }} <span class="text-xs font-medium text-slate-400">mahasiswa</span></p>
                </div>
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Entri Jawaban</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-[#0B6B3A] tabular-nums">{{ number_format($totalJawaban) }}</p>
                </div>
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-Rata Skor</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-[#20A464] tabular-nums">{{ number_format($rataRataKeseluruhan, 2) }}</p>
                </div>
            </div>

            @if ($quisioner->type === 'edom' && $dosenFilterList->isNotEmpty())
                <div class="border-t border-emerald-100 bg-emerald-50/40 px-6 py-3.5 print:hidden">
                    <form method="GET" action="{{ route('admin.laporan.show', $quisioner) }}" class="flex flex-wrap items-center gap-3">
                        <label for="nipy" class="text-xs font-semibold text-emerald-950">Filter Laporan per Dosen:</label>
                        <select id="nipy" name="nipy" onchange="this.form.submit()" class="rounded-xl border border-emerald-200 bg-white px-3.5 py-2 text-sm text-slate-800 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                            <option value="">Semua Dosen</option>
                            @foreach ($dosenFilterList as $d)
                                <option value="{{ $d['nipy'] }}" @selected($filterNipy === $d['nipy'])>
                                    {{ $d['nama'] }} ({{ $d['nipy'] }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        {{-- Card Grafik Visualisasi (Chart.js Lokal via Vite) --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="print-avoid-break rounded-xl border border-emerald-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-[#062419]">Distribusi Jumlah Jawaban per Skor</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Akumulasi seluruh jawaban pilihan ganda pada quisioner ini</p>
                </div>
                @if (count($chartDistribusi['labels']) > 0 && array_sum($chartDistribusi['data']) > 0)
                    <div class="relative h-72">
                        <canvas id="chartDistribusiSkor" data-chart='@json($chartDistribusi)'></canvas>
                    </div>
                @else
                    <div class="flex h-60 items-center justify-center rounded-xl border border-dashed border-emerald-200 bg-[#F4F7F5] text-xs text-slate-500">
                        Belum ada data skor jawaban untuk ditampilkan pada grafik.
                    </div>
                @endif
            </div>

            <div class="print-avoid-break rounded-xl border border-emerald-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-[#062419]">Rata-Rata Skor per Pertanyaan</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Perbandingan capaian skor rata-rata tiap butir pertanyaan (P1, P2, ...)</p>
                </div>
                @if (count($chartPerPertanyaan['labels']) > 0 && array_sum($chartPerPertanyaan['data']) > 0)
                    <div class="relative h-72">
                        <canvas id="chartRataPertanyaan" data-chart='@json($chartPerPertanyaan)'></canvas>
                    </div>
                @else
                    <div class="flex h-60 items-center justify-center rounded-xl border border-dashed border-emerald-200 bg-[#F4F7F5] text-xs text-slate-500">
                        Belum ada data rata-rata pertanyaan untuk ditampilkan pada grafik.
                    </div>
                @endif
            </div>
        </div>

        {{-- Card Tabel Rekapitulasi Skor per Pertanyaan --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            <div class="border-b border-emerald-100 px-6 py-4">
                <h3 class="text-sm font-bold text-[#062419]">Tabel Rekapitulasi Jumlah Skor Jawaban</h3>
                <p class="mt-0.5 text-xs text-slate-500">Distribusi frekuensi tiap nilai skor dan rata-rata per butir pertanyaan</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-16 px-5 py-3.5 text-center">No</th>
                            <th class="w-40 px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Pertanyaan</th>
                            @foreach ($skorColumns as $col)
                                <th class="w-24 px-3 py-3.5 text-center" title="{{ $col['keterangan'] }}">
                                    <span class="block">Skor {{ $col['skor'] }}</span>
                                    <span class="text-[10px] font-medium normal-case text-emerald-700/80">({{ $col['huruf'] }})</span>
                                </th>
                            @endforeach
                            <th class="w-24 px-5 py-3.5 text-center">Total</th>
                            <th class="w-28 px-5 py-3.5 text-center">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rekapGanda as $row)
                            <tr class="transition hover:bg-emerald-50/50">
                                <td class="px-5 py-3.5 text-center font-bold text-[#0B6B3A]">P{{ $row['nomor'] }}</td>
                                <td class="px-5 py-3.5 text-xs font-semibold text-slate-700">{{ $row['kategori'] }}</td>
                                <td class="px-5 py-3.5 leading-relaxed text-slate-800">{{ $row['isi_pertanyaan'] }}</td>
                                @foreach ($skorColumns as $col)
                                    <td class="px-3 py-3.5 text-center font-mono text-xs text-slate-700 tabular-nums">
                                        {{ number_format($row['distribusi'][$col['skor']] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="px-5 py-3.5 text-center font-semibold text-[#062419] tabular-nums">
                                    {{ number_format($row['total']) }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-[#0B6B3A] ring-1 ring-emerald-200/80 tabular-nums">
                                        {{ number_format($row['rata_rata'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + count($skorColumns) }}" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Belum ada pertanyaan bertipe Pilihan Ganda pada quisioner ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (! empty($rekapGanda))
                        <tfoot class="border-t-2 border-emerald-200 bg-emerald-50/80 text-xs font-bold text-[#062419]">
                            <tr>
                                <td colspan="3" class="px-5 py-3.5 text-right uppercase tracking-wider">Total Akumulasi Skor</td>
                                @foreach ($skorColumns as $col)
                                    <td class="px-3 py-3.5 text-center font-mono tabular-nums">
                                        {{ number_format($totalBySkor[$col['skor']] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="px-5 py-3.5 text-center tabular-nums">
                                    {{ number_format(array_sum($totalBySkor)) }}
                                </td>
                                <td class="px-5 py-3.5 text-center text-[#0B6B3A] tabular-nums">
                                    {{ number_format($rataRataKeseluruhan, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Card Daftar Jawaban Esai (jika ada) --}}
        @if ($jawabanEsai->isNotEmpty())
            <div class="print-avoid-break overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
                <div class="border-b border-emerald-100 bg-emerald-50/60 px-6 py-4">
                    <h3 class="text-sm font-bold text-[#062419]">Rekap Jawaban Isian / Esai</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Menampilkan maksimal 100 jawaban teks terbaru dari responden</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($jawabanEsai as $esai)
                        <div class="px-6 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                                <span class="font-semibold text-[#0B6B3A]">{{ $esai->isi_pertanyaan }}</span>
                                <span>{{ $esai->tanggal?->format('d M Y') ?? '-' }}</span>
                            </div>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-800">{{ $esai->isi_jawaban }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
