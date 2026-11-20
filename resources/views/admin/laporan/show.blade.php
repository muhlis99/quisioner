@extends('layouts.admin')

@section('title', 'Laporan Hasil Quisioner - Admin')
@section('page-title', 'Hasil Quisioner')
@section('page-subtitle', $quisioner->judul_quisioner)

@section('header-actions')
    <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        &larr; Kembali ke Daftar Laporan
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Header Info + Filter Dosen (khusus EDOM) --}}
        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold uppercase text-indigo-700 ring-1 ring-indigo-200">
                        {{ $quisioner->type }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-slate-700">
                        TA {{ $quisioner->tahun_ajaran }} &middot; Semester {{ $quisioner->semester }}
                    </span>
                </div>
                <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $quisioner->judul_quisioner }}</h2>
            </div>

            @if ($quisioner->type === 'edom' && $dosenFilterList->isNotEmpty())
                <form method="GET" action="{{ route('admin.laporan.show', $quisioner) }}" class="flex flex-wrap items-center gap-2">
                    <label for="nipy" class="text-xs font-semibold text-slate-600">Filter Dosen:</label>
                    <select id="nipy" name="nipy" onchange="this.form.submit()" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Dosen</option>
                        @foreach ($dosenFilterList as $d)
                            <option value="{{ $d['nipy'] }}" @selected($filterNipy === $d['nipy'])>
                                {{ $d['nama'] }} ({{ $d['nipy'] }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        {{-- Kartu Statistik Ringkas --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Responden (Mahasiswa)</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalResponden) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Entri Jawaban</p>
                <p class="mt-2 text-2xl font-bold text-indigo-600">{{ number_format($totalJawaban) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Rata-Rata Skor Keseluruhan</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($rataRataKeseluruhan, 2) }}</p>
            </div>
        </div>

        {{-- Visualisasi Diagram (Chart.js) --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Distribusi Jumlah Jawaban per Skor</h3>
                    <p class="text-xs text-slate-500">Akumulasi seluruh jawaban pilihan ganda pada quisioner ini</p>
                </div>
                @if (count($chartDistribusi['labels']) > 0 && array_sum($chartDistribusi['data']) > 0)
                    <div class="relative h-72">
                        <canvas id="chartDistribusiSkor"></canvas>
                    </div>
                @else
                    <div class="flex h-60 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500">
                        Belum ada data skor jawaban pada <code class="mx-1 rounded bg-white px-1.5 py-0.5">tb_hasil</code> untuk ditampilkan pada grafik.
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Rata-Rata Skor per Pertanyaan</h3>
                    <p class="text-xs text-slate-500">Perbandingan capaian skor rata-rata tiap butir pertanyaan (P1, P2, ...)</p>
                </div>
                @if (count($chartPerPertanyaan['labels']) > 0 && array_sum($chartPerPertanyaan['data']) > 0)
                    <div class="relative h-72">
                        <canvas id="chartRataPertanyaan"></canvas>
                    </div>
                @else
                    <div class="flex h-60 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500">
                        Belum ada data rata-rata pertanyaan untuk ditampilkan pada grafik.
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabel Angka Rekapitulasi Skor per Pertanyaan --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-900">Tabel Rekapitulasi Jumlah Skor Jawaban</h3>
                <p class="text-xs text-slate-500">Distribusi frekuensi tiap nilai skor dari <code class="rounded bg-slate-100 px-1">tb_hasil</code></p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-14 px-4 py-3.5 text-center">No</th>
                            <th class="w-40 px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Pertanyaan</th>
                            @foreach ($skorColumns as $col)
                                <th class="w-24 px-3 py-3.5 text-center" title="{{ $col['keterangan'] }}">
                                    <span class="block">Skor {{ $col['skor'] }}</span>
                                    <span class="text-[10px] font-normal normal-case text-slate-400">({{ $col['huruf'] }})</span>
                                </th>
                            @endforeach
                            <th class="w-24 px-4 py-3.5 text-center">Total</th>
                            <th class="w-24 px-4 py-3.5 text-center">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rekapGanda as $row)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3 text-center font-semibold text-slate-500">P{{ $row['nomor'] }}</td>
                                <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $row['kategori'] }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $row['isi_pertanyaan'] }}</td>
                                @foreach ($skorColumns as $col)
                                    <td class="px-3 py-3 text-center font-mono text-xs text-slate-700">
                                        {{ number_format($row['distribusi'][$col['skor']] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center font-semibold text-slate-900">
                                    {{ number_format($row['total']) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        {{ number_format($row['rata_rata'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + count($skorColumns) }}" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada pertanyaan bertipe Pilihan Ganda pada quisioner ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (! empty($rekapGanda))
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 text-xs font-bold text-slate-800">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right uppercase">Total Akumulasi Skor</td>
                                @foreach ($skorColumns as $col)
                                    <td class="px-3 py-3 text-center font-mono">
                                        {{ number_format($totalBySkor[$col['skor']] ?? 0) }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center">
                                    {{ number_format(array_sum($totalBySkor)) }}
                                </td>
                                <td class="px-4 py-3 text-center text-emerald-700">
                                    {{ number_format($rataRataKeseluruhan, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Daftar Jawaban Esai (jika ada) --}}
        @if ($jawabanEsai->isNotEmpty())
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-sm font-bold text-slate-900">Rekap Jawaban Isian / Esai</h3>
                    <p class="text-xs text-slate-500">Menampilkan maksimal 100 jawaban teks terbaru dari responden</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($jawabanEsai as $esai)
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                                <span class="font-semibold text-slate-700">{{ $esai->isi_pertanyaan }}</span>
                                <span>{{ $esai->tanggal?->format('d M Y') ?? '-' }}</span>
                            </div>
                            <p class="mt-1.5 text-sm text-slate-800">{{ $esai->isi_jawaban }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') return;

            const distribusiData = @json($chartDistribusi);
            const pertanyaanData = @json($chartPerPertanyaan);

            const ctxDist = document.getElementById('chartDistribusiSkor');
            if (ctxDist && distribusiData.labels.length > 0) {
                new Chart(ctxDist, {
                    type: 'bar',
                    data: {
                        labels: distribusiData.labels,
                        datasets: [{
                            label: 'Jumlah Jawaban',
                            data: distribusiData.data,
                            backgroundColor: ['#f43f5e', '#f59e0b', '#3b82f6', '#10b981', '#6366f1', '#8b5cf6'],
                            borderRadius: 8,
                            maxBarThickness: 56,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                            },
                        },
                    },
                });
            }

            const ctxPertanyaan = document.getElementById('chartRataPertanyaan');
            if (ctxPertanyaan && pertanyaanData.labels.length > 0) {
                new Chart(ctxPertanyaan, {
                    type: 'bar',
                    data: {
                        labels: pertanyaanData.labels,
                        datasets: [{
                            label: 'Rata-rata Skor',
                            data: pertanyaanData.data,
                            backgroundColor: 'rgba(79, 70, 229, 0.85)',
                            borderRadius: 8,
                            maxBarThickness: 44,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: (items) => {
                                        const idx = items[0]?.dataIndex ?? 0;
                                        return `${pertanyaanData.labels[idx]}: ${pertanyaanData.fullLabels[idx] || ''}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                            },
                        },
                    },
                });
            }
        });
    </script>
@endpush
