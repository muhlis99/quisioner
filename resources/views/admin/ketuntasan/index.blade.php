@extends('layouts.admin')

@section('title', 'Monitoring Ketuntasan & Nilai Per-Mahasiswa - Admin')
@section('page-title', 'Monitoring Ketuntasan')
@section('page-subtitle', 'Rekapitulasi ketuntasan kuisioner')

@section('content')
    @php
        $activeFilterParams = array_filter([
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
            'id_quisioner' => $quisionerId,
        ], fn ($v) => $v !== '');

        $allQueryParams = array_filter([
            'search' => $search,
            'ketuntasan' => $status,
            'keterangan' => $keteranganFilter,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
            'id_quisioner' => $quisionerId,
            'sort' => $sort !== 'nim_asc' ? $sort : '',
        ], fn ($v) => $v !== '');

        $advancedFilterCount = 0;
        if ($tahunAjaran !== '') {
            $advancedFilterCount++;
        }
        if ($semester !== '') {
            $advancedFilterCount++;
        }
        if ($quisionerId !== '') {
            $advancedFilterCount++;
        }
        if ($sort !== 'nim_asc') {
            $advancedFilterCount++;
        }

        $hasAnyFilter = ! empty($allQueryParams);
        $advancedOpenDefault = $advancedFilterCount > 0;

        $sortLabels = [
            'nim_asc' => 'NIM',
            'nama_asc' => 'Nama A–Z',
            'rata_desc' => 'Nilai Tertinggi',
            'rata_asc' => 'Nilai Terendah',
            'skor_desc' => 'Total Skor Tertinggi',
            'tuntas_desc' => 'Ketuntasan Tertinggi',
        ];
        $keteranganLabels = [
            'sesuai' => 'Sesuai (80–100)',
            'tidak_sesuai' => 'Tidak Sesuai (< 80)',
            'belum_dinilai' => 'Belum Dinilai',
        ];

        $activeChips = [];
        if ($search !== '') {
            $activeChips[] = ['key' => 'search', 'label' => 'Cari', 'value' => $search];
        }
        if ($status !== '') {
            $activeChips[] = ['key' => 'ketuntasan', 'label' => 'Status', 'value' => $status === '1' ? 'Tuntas' : 'Belum Tuntas'];
        }
        if ($keteranganFilter !== '') {
            $activeChips[] = ['key' => 'keterangan', 'label' => 'Keterangan', 'value' => $keteranganLabels[$keteranganFilter] ?? $keteranganFilter];
        }
        if ($tahunAjaran !== '') {
            $activeChips[] = ['key' => 'tahun_ajaran', 'label' => 'TA', 'value' => $tahunAjaran];
        }
        if ($semester !== '') {
            $activeChips[] = ['key' => 'semester', 'label' => 'Semester', 'value' => ucfirst($semester)];
        }
        if ($quisionerId !== '') {
            $selectedQ = $quisionerList->firstWhere('id', (int) $quisionerId);
            $activeChips[] = ['key' => 'id_quisioner', 'label' => 'Quisioner', 'value' => $selectedQ ? $selectedQ->judul_quisioner : '#' . $quisionerId];
        }
        if ($sort !== 'nim_asc') {
            $activeChips[] = ['key' => 'sort', 'label' => 'Urutkan', 'value' => $sortLabels[$sort] ?? $sort];
        }
    @endphp

    <div class="space-y-6">
        {{-- 4 Summary Cards Ringkas Per-Mahasiswa --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mahasiswa</p>
                <p class="mt-2 text-3xl font-extrabold text-[#062419] tabular-nums">{{ number_format($totalMahasiswa) }}</p>
                <p class="mt-1.5 text-xs text-slate-500">Acuan: {{ number_format($totalQuisionerWajib) }} quisioner aktif</p>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mahasiswa Tuntas</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-extrabold text-[#20A464] tabular-nums">{{ number_format($totalTuntas) }}</p>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-[#0B6B3A] tabular-nums">
                        {{ number_format($persentase, 1) }}%
                    </span>
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Telah menyelesaikan seluruh tanggungan</p>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Belum Tuntas</p>
                <p class="mt-2 text-3xl font-extrabold text-amber-600 tabular-nums">{{ number_format($totalBelum) }}</p>
                <p class="mt-1.5 text-xs text-slate-500">
                    Kelayakan: <strong class="text-emerald-700">{{ number_format($totalSesuai) }} Sesuai</strong>
                    &middot; <strong class="text-rose-600">{{ number_format($totalTidakSesuai) }} Tidak Sesuai</strong>
                </p>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-white via-white to-emerald-50/80 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#0B6B3A]">Rata-Rata Nilai (0–100)</p>
                <div class="mt-2 flex items-center gap-2.5">
                    <p class="text-3xl font-extrabold tabular-nums {{ $kelayakanKeseluruhan['value_class'] }}">
                        {{ $nilaiSkala100Keseluruhan !== null ? $nilaiSkala100Keseluruhan : '-' }}
                    </p>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $kelayakanKeseluruhan['badge_class'] }}">
                        {{ $kelayakanKeseluruhan['label'] }}
                    </span>
                </div>
                <p class="mt-1.5 text-xs text-slate-600">
                    Skor: <strong class="text-[#062419] tabular-nums">{{ number_format($rataRataKeseluruhan, 2) }}</strong> / {{ rtrim(rtrim(number_format($maxSkor, 2, '.', ''), '0'), '.') }}
                    &middot; Total {{ number_format($grandTotalSkor) }}
                </p>
            </div>
        </div>

        {{-- Card Tabel Rekap Per-Mahasiswa --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            {{-- Toolbar Filter Ringkas (1 Baris Utama + Panel Filter Lanjutan Collapsible + Active Chips) --}}
            <div class="border-b border-emerald-100 px-5 py-4 sm:px-6">
                <form method="GET" action="{{ route('admin.ketuntasan.index') }}">
                    @if ($perPage !== '25')
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @endif
                    {{-- Baris Utama (1 Baris Rapi di Desktop, Responsif di Mobile) --}}
                    <div class="flex flex-wrap items-center gap-2 lg:flex-nowrap">
                        <div class="relative min-w-[180px] flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari NIM atau nama mahasiswa..."
                                class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-3 pl-9 text-xs text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                            >
                        </div>

                        <select name="ketuntasan" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                            <option value="">Semua Status</option>
                            <option value="1" @selected($status === '1')>Tuntas</option>
                            <option value="0" @selected($status === '0')>Belum Tuntas</option>
                        </select>

                        <select name="keterangan" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                            <option value="">Semua Keterangan</option>
                            <option value="sesuai" @selected($keteranganFilter === 'sesuai')>Sesuai (80–100)</option>
                            <option value="tidak_sesuai" @selected($keteranganFilter === 'tidak_sesuai')>Tidak Sesuai (&lt; 80)</option>
                            <option value="belum_dinilai" @selected($keteranganFilter === 'belum_dinilai')>Belum Dinilai</option>
                        </select>

                        <button
                            id="btn-toggle-advanced-filter"
                            type="button"
                            aria-expanded="{{ $advancedOpenDefault ? 'true' : 'false' }}"
                            aria-controls="panel-advanced-filter"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-semibold transition {{ $advancedFilterCount > 0 ? 'border-[#20A464]/50 bg-emerald-50 text-[#0B6B3A]' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-200 hover:bg-emerald-50/50' }}"
                        >
                            <svg class="h-3.5 w-3.5 text-[#0B6B3A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                            </svg>
                            <span>Filter Lanjutan</span>
                            @if ($advancedFilterCount > 0)
                                <span class="inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-[#20A464] px-1 text-[10px] font-bold text-white tabular-nums">
                                    {{ $advancedFilterCount }}
                                </span>
                            @endif
                            <svg id="icon-advanced-chevron" class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200 {{ $advancedOpenDefault ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <button type="submit" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#20A464] px-4 py-2 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                            <span>Filter</span>
                        </button>

                        @if ($hasAnyFilter)
                            <a
                                href="{{ route('admin.ketuntasan.index') }}"
                                title="Reset semua filter"
                                aria-label="Reset semua filter"
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    {{-- Panel Filter Lanjutan (Collapsible Grid 4 Kolom) --}}
                    <div
                        id="panel-advanced-filter"
                        class="{{ $advancedOpenDefault ? '' : 'hidden' }} mt-3 rounded-xl border border-emerald-100 bg-[#F4F7F5]/90 p-3.5"
                    >
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label for="filter-kt-ta" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-emerald-900/80">Tahun Ajaran</label>
                                <select id="filter-kt-ta" name="tahun_ajaran" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                                    <option value="">Semua TA</option>
                                    @foreach ($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" @selected($tahunAjaran === (string) $ta)>{{ $ta }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filter-kt-semester" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-emerald-900/80">Semester</label>
                                <select id="filter-kt-semester" name="semester" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                                    <option value="">Semua Semester</option>
                                    <option value="ganjil" @selected($semester === 'ganjil')>Ganjil</option>
                                    <option value="genap" @selected($semester === 'genap')>Genap</option>
                                </select>
                            </div>

                            <div>
                                <label for="filter-kt-quisioner" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-emerald-900/80">Quisioner</label>
                                <select id="filter-kt-quisioner" name="id_quisioner" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                                    <option value="">Semua Quisioner</option>
                                    @foreach ($quisionerList as $q)
                                        <option value="{{ $q->id }}" @selected($quisionerId === (string) $q->id)>
                                            {{ $q->judul_quisioner }} ({{ $q->tahun_ajaran }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filter-kt-sort" class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-emerald-900/80">Urutkan</label>
                                <select id="filter-kt-sort" name="sort" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none">
                                    <option value="nim_asc" @selected($sort === 'nim_asc')>Urutkan: NIM</option>
                                    <option value="nama_asc" @selected($sort === 'nama_asc')>Urutkan: Nama A–Z</option>
                                    <option value="rata_desc" @selected($sort === 'rata_desc')>Urutkan: Nilai Tertinggi</option>
                                    <option value="rata_asc" @selected($sort === 'rata_asc')>Urutkan: Nilai Terendah</option>
                                    <option value="skor_desc" @selected($sort === 'skor_desc')>Urutkan: Total Skor Tertinggi</option>
                                    <option value="tuntas_desc" @selected($sort === 'tuntas_desc')>Urutkan: Ketuntasan Tertinggi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Active Filter Chips --}}
                @if (! empty($activeChips))
                    <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-emerald-100/70 pt-3">
                        <span class="mr-1 text-[11px] font-semibold text-slate-400">Filter aktif:</span>
                        @foreach ($activeChips as $chip)
                            @php
                                $withoutChip = $allQueryParams;
                                unset($withoutChip[$chip['key']]);
                            @endphp
                            <a
                                href="{{ route('admin.ketuntasan.index', $withoutChip) }}"
                                title="Hapus filter {{ $chip['label'] }}"
                                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-[#0B6B3A] transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700"
                            >
                                <span class="text-slate-500">{{ $chip['label'] }}:</span>
                                <span class="max-w-[160px] truncate">{{ $chip['value'] }}</span>
                                <span aria-hidden="true" class="font-bold">&times;</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-14 px-6 py-3.5">No</th>
                            <th class="w-40 px-6 py-3.5">NIM</th>
                            <th class="px-6 py-3.5">Nama Mahasiswa</th>
                            <th class="w-56 px-6 py-3.5 text-center">Status Ketuntasan</th>
                            <th class="w-32 px-6 py-3.5 text-right">Rata-Rata</th>
                            <th class="w-40 px-6 py-3.5 text-center">Keterangan</th>
                            <th class="w-28 px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rekapMahasiswa as $index => $row)
                            <tr class="transition hover:bg-emerald-50/50">
                                <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $rekapMahasiswa->firstItem() + $index }}</td>
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-[#0B6B3A]">{{ $row->nim }}</td>
                                <td class="px-6 py-4 font-semibold text-[#062419]">{{ $row->nama }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center gap-1.5">
                                        @if ($row->is_tuntas)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/80">
                                                <span class="h-1.5 w-1.5 rounded-full bg-[#20A464]"></span>
                                                Tuntas ({{ $row->jumlah_tuntas }}/{{ $row->total_wajib }})
                                            </span>
                                        @elseif ($row->is_sebagian)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200/80">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Sebagian ({{ $row->jumlah_tuntas }}/{{ $row->total_wajib }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-amber-200/80">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Belum Tuntas (0/{{ $row->total_wajib }})
                                            </span>
                                        @endif

                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200">
                                                <div
                                                    class="h-full rounded-full {{ $row->is_tuntas ? 'bg-[#20A464]' : ($row->is_sebagian ? 'bg-amber-500' : 'bg-slate-300') }}"
                                                    style="width: {{ $row->progres_persen }}%"
                                                ></div>
                                            </div>
                                            <span class="font-mono text-[11px] text-slate-500 tabular-nums">{{ $row->progres_persen }}%</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($row->jumlah_jawaban > 0)
                                        <span class="font-mono text-sm font-bold text-[#062419] tabular-nums">{{ number_format($row->rata_rata_skor, 2) }}</span>
                                    @else
                                        <span class="text-xs font-medium text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $row->kelayakan['badge_class'] }}">
                                        {{ $row->kelayakan['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('admin.ketuntasan.show', array_merge(['nim' => $row->nim], $activeFilterParams)) }}"
                                        class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#0B6B3A] transition hover:bg-[#20A464] hover:text-white"
                                    >
                                        <span>Detail</span>
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Tidak ditemukan data mahasiswa sesuai filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination: Per-Page Selector, Info Data, Jump to Page, & Tombol Halaman --}}
            @if ($rekapMahasiswa->total() > 0)
                @php
                    $paginationHiddenParams = array_filter([
                        'search' => $search,
                        'ketuntasan' => $status,
                        'keterangan' => $keteranganFilter,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                        'id_quisioner' => $quisionerId,
                        'sort' => $sort !== 'nim_asc' ? $sort : '',
                    ], fn ($v) => $v !== '');
                @endphp
                <div class="flex flex-col gap-3.5 border-t border-emerald-100 bg-emerald-50/30 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        {{-- Kiri: Selector Jumlah Baris per Halaman & Rentang Data --}}
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                            <form method="GET" action="{{ route('admin.ketuntasan.index') }}" class="inline-flex items-center gap-2">
                                @foreach ($paginationHiddenParams as $paramKey => $paramVal)
                                    <input type="hidden" name="{{ $paramKey }}" value="{{ $paramVal }}">
                                @endforeach
                                <label for="kt-per-page" class="font-semibold text-emerald-950">Tampilkan</label>
                                <select
                                    id="kt-per-page"
                                    name="per_page"
                                    data-auto-submit
                                    class="rounded-xl border border-emerald-200 bg-white px-2.5 py-1.5 text-xs font-bold text-[#062419] shadow-2xs transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                                >
                                    @foreach ($perPageOptions as $opt)
                                        <option value="{{ $opt }}" @selected($perPage === (string) $opt)>
                                            {{ $opt === 'all' ? 'Semua' : $opt . ' / hal' }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                            <span class="text-slate-300 hidden sm:inline">|</span>

                            <p class="tabular-nums">
                                Menampilkan
                                <strong class="font-bold text-[#062419]">{{ number_format($rekapMahasiswa->firstItem() ?? 0) }}</strong>&ndash;<strong class="font-bold text-[#062419]">{{ number_format($rekapMahasiswa->lastItem() ?? 0) }}</strong>
                                dari
                                <strong class="font-bold text-[#062419]">{{ number_format($rekapMahasiswa->total()) }}</strong>
                                mahasiswa
                            </p>
                        </div>

                        {{-- Kanan: Jump to Page (Muncul jika lebih dari 1 halaman) --}}
                        @if ($rekapMahasiswa->lastPage() > 1)
                            <form method="GET" action="{{ route('admin.ketuntasan.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                                @foreach ($paginationHiddenParams as $paramKey => $paramVal)
                                    <input type="hidden" name="{{ $paramKey }}" value="{{ $paramVal }}">
                                @endforeach
                                <input type="hidden" name="per_page" value="{{ $perPage }}">

                                <label for="kt-jump-page" class="font-semibold text-emerald-950">Hal</label>
                                <input
                                    id="kt-jump-page"
                                    type="number"
                                    name="page"
                                    min="1"
                                    max="{{ $rekapMahasiswa->lastPage() }}"
                                    value="{{ $rekapMahasiswa->currentPage() }}"
                                    class="w-16 rounded-xl border border-emerald-200 bg-white px-2.5 py-1.5 text-center font-mono text-xs font-bold text-[#062419] shadow-2xs transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                                >
                                <span class="font-medium text-slate-500 tabular-nums">dari {{ number_format($rekapMahasiswa->lastPage()) }}</span>
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#20A464] px-3 py-1.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-[#17804D]"
                                >
                                    Lompat
                                </button>
                            </form>
                        @endif
                    </div>

                    @if ($rekapMahasiswa->hasPages())
                        <div class="border-t border-emerald-100/80 pt-3">
                            {{ $rekapMahasiswa->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
