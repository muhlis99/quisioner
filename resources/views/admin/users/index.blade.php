@extends('layouts.admin')

@section('title', 'Kelola Pengguna - Admin')
@section('page-title', 'Kelola Pengguna')
@section('page-subtitle', 'Kelola akun Administrator secara manual dan Generate Akun Mahasiswa otomatis dari SIAKAD')

@section('content')
    <div class="space-y-5">
        {{-- Navigasi 2 Tab Terpisah: Non-Mahasiswa vs Mahasiswa --}}
        <div class="flex flex-wrap items-center gap-2 rounded-xl border border-emerald-100 bg-white p-2 shadow-sm">
            <a
                href="{{ route('admin.users.index', ['tab' => 'non-mahasiswa']) }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $tab === 'non-mahasiswa' ? 'bg-[#062419] text-white shadow-xs' : 'text-slate-600 hover:bg-emerald-50 hover:text-[#0B6B3A]' }}"
            >
                <svg class="h-4 w-4 {{ $tab === 'non-mahasiswa' ? 'text-[#20A464]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span>Non-Mahasiswa (Admin)</span>
                <span class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums {{ $tab === 'non-mahasiswa' ? 'bg-[#20A464] text-white' : 'bg-emerald-50 text-[#0B6B3A]' }}">
                    {{ number_format($totalNonMahasiswa) }}
                </span>
            </a>

            <a
                href="{{ route('admin.users.index', ['tab' => 'mahasiswa']) }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $tab === 'mahasiswa' ? 'bg-[#062419] text-white shadow-xs' : 'text-slate-600 hover:bg-emerald-50 hover:text-[#0B6B3A]' }}"
            >
                <svg class="h-4 w-4 {{ $tab === 'mahasiswa' ? 'text-[#20A464]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84" />
                </svg>
                <span>Mahasiswa</span>
                <span class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums {{ $tab === 'mahasiswa' ? 'bg-[#20A464] text-white' : 'bg-emerald-50 text-[#0B6B3A]' }}">
                    {{ number_format($totalAkunMahasiswa) }}
                </span>
                @if ($belumPunyaAkun > 0)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800">
                        +{{ number_format($belumPunyaAkun) }} belum dibuat
                    </span>
                @endif
            </a>
        </div>

        {{-- Info Ringkas Khusus Tab Mahasiswa --}}
        @if ($tab === 'mahasiswa')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mahasiswa SIAKAD</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-[#062419] tabular-nums">{{ number_format($totalMahasiswaSiakad) }}</p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sudah Memiliki Akun</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-[#20A464] tabular-nums">{{ number_format($totalAkunMahasiswa) }}</p>
                </div>
                <div class="rounded-xl border {{ $belumPunyaAkun > 0 ? 'border-amber-200 bg-amber-50/40' : 'border-emerald-200 bg-emerald-50/40' }} p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider {{ $belumPunyaAkun > 0 ? 'text-amber-800' : 'text-[#0B6B3A]' }}">Belum Memiliki Akun</p>
                    <p class="mt-1.5 text-2xl font-extrabold {{ $belumPunyaAkun > 0 ? 'text-amber-600' : 'text-[#0B6B3A]' }} tabular-nums">{{ number_format($belumPunyaAkun) }}</p>
                </div>
            </div>
        @endif

        {{-- Card Tabel Utama --}}
        <div class="overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
            {{-- Toolbar Atas Card --}}
            <div class="flex flex-col gap-3 border-b border-emerald-100 px-5 py-4 xl:flex-row xl:items-center xl:justify-between sm:px-6">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="relative min-w-[220px] flex-1 sm:max-w-xs">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18a7.5 7.5 0 006.15-3.35z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="{{ $tab === 'mahasiswa' ? 'Cari NIM atau nama mahasiswa...' : 'Cari username admin...' }}"
                            class="w-full rounded-xl border border-slate-200 bg-[#F4F7F5]/60 py-2 pr-4 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-[#20A464] focus:bg-white focus:ring-2 focus:ring-[#20A464]/20 focus:outline-none"
                        >
                    </div>

                    <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-[#20A464] focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($status === '1')>Aktif</option>
                        <option value="0" @selected($status === '0')>Nonaktif</option>
                    </select>

                    <button type="submit" class="rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-2xs transition hover:bg-[#17804D]">
                        Cari
                    </button>

                    @if ($search !== '' || $status !== '')
                        <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </form>

                {{-- Tombol Aksi Utama per Tab --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if ($tab === 'non-mahasiswa')
                        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Tambah Pengguna</span>
                        </a>
                    @else
                        <button
                            id="btn-generate-akun-mahasiswa"
                            type="button"
                            data-url="{{ route('admin.users.generate-mahasiswa') }}"
                            data-pending="{{ $belumPunyaAkun }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                            </svg>
                            <span>Generate Akun Otomatis</span>
                        </button>

                        @if ($totalAkunMahasiswa > 0)
                            <button
                                id="btn-reset-massal-mahasiswa"
                                type="button"
                                data-url="{{ route('admin.users.reset-mahasiswa-massal') }}"
                                data-count="{{ $totalAkunMahasiswa }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                <span>Reset Massal ke NIM</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Tabel Daftar Pengguna --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-emerald-100/80 text-left text-sm">
                    <thead class="bg-emerald-50 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <tr>
                            <th class="w-14 px-6 py-3.5">No</th>
                            <th class="w-48 px-6 py-3.5">{{ $tab === 'mahasiswa' ? 'NIM (Username)' : 'Username' }}</th>
                            <th class="px-6 py-3.5">{{ $tab === 'mahasiswa' ? 'Nama Mahasiswa' : 'Peran / Keterangan' }}</th>
                            <th class="w-28 px-6 py-3.5 text-center">Status</th>
                            <th class="w-72 px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $index => $user)
                            @php $isMhs = $user->mahasiswa !== null; @endphp
                            <tr class="transition hover:bg-emerald-50/50">
                                <td class="px-6 py-4 text-slate-500 tabular-nums">{{ $users->firstItem() + $index }}</td>
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-[#062419]">
                                    {{ $user->username }}
                                    @if ((int) auth()->id() === (int) $user->id)
                                        <span class="ml-1.5 rounded-full bg-emerald-50 px-2 py-0.5 font-sans text-[10px] font-bold text-[#0B6B3A] ring-1 ring-emerald-200">Anda</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($isMhs)
                                        <p class="font-semibold text-slate-900">{{ $user->mahasiswa->nama }}</p>
                                        <p class="text-[11px] text-slate-400">Password default: NIM mahasiswa</p>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200/80">
                                            Admin / Non-Mahasiswa
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($user->status === '1')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/80">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200/80">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        @if (! $isMhs)
                                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#0B6B3A] transition hover:bg-emerald-100">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                </svg>
                                                <span>Edit</span>
                                            </a>
                                        @endif

                                        <button
                                            type="button"
                                            data-reset-password-btn
                                            data-url="{{ route('admin.users.reset-password', $user) }}"
                                            data-username="{{ $user->username }}"
                                            data-is-mahasiswa="{{ $isMhs ? '1' : '0' }}"
                                            class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                            </svg>
                                            <span>{{ $isMhs ? 'Reset ke NIM' : 'Reset Password' }}</span>
                                        </button>

                                        @if ((int) auth()->id() !== (int) $user->id)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.destroy', $user) }}"
                                                data-confirm-delete
                                                data-confirm-title="Hapus Akun Pengguna?"
                                                data-confirm-text="Akun '{{ $user->username }}' akan dihapus permanen dari tb_users."
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                    @if ($tab === 'mahasiswa')
                                        Belum ada akun mahasiswa pada <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">tb_users</code>. Klik <strong>Generate Akun Otomatis</strong> untuk membuatkan akun seluruh mahasiswa dari SIAKAD.
                                    @else
                                        Belum ada data akun non-mahasiswa yang sesuai filter.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-emerald-100 bg-emerald-50/30 px-6 py-3.5">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
