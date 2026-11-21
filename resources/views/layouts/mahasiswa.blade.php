<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-[#F4F7FB] text-slate-800 antialiased">
    @php
        $namaMahasiswa = session('mahasiswa_nama') ?? auth()->user()?->mahasiswa?->nama ?? auth()->user()?->username ?? 'Mahasiswa';
        $nimMahasiswa = session('mahasiswa_nim') ?? auth()->user()?->mahasiswa?->nim ?? auth()->user()?->username ?? '-';
        $inisialMahasiswa = strtoupper(mb_substr($namaMahasiswa, 0, 1));

        $linkClass = fn (bool $active): string => $active
            ? 'flex items-center gap-3 rounded-xl border-l-4 border-[#60A5FA] bg-white/15 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs backdrop-blur-xs transition'
            : 'flex items-center gap-3 rounded-xl border-l-4 border-transparent px-3.5 py-2.5 text-sm font-medium text-blue-100/85 transition hover:bg-white/10 hover:text-white';
    @endphp

    <div class="min-h-screen lg:flex">
        {{-- Overlay Mobile --}}
        <div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-[#0F294A]/65 backdrop-blur-xs transition-opacity lg:hidden"></div>

        {{-- Sidebar Portal Mahasiswa (Deep Royal Blue) --}}
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-gradient-to-b from-[#0F294A] via-[#153162] to-[#1E3A8A] text-white shadow-2xl transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:shadow-none">
            {{-- Brand --}}
            <div class="flex h-18 items-center justify-between border-b border-white/10 px-5">
                <a
                    href="{{ route('mahasiswa.dashboard') }}"
                    title="STAI Nurul Abror Al-Robbaniyin — Portal Mahasiswa"
                    class="group flex min-w-0 items-center gap-3"
                >
                    <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/95 p-0.5 shadow-md ring-2 ring-blue-300/40 transition-all duration-300 group-hover:scale-105 group-hover:rotate-3 group-hover:ring-[#60A5FA] group-hover:shadow-[0_0_18px_rgba(96,165,250,0.6)]">
                        <img
                            src="{{ asset('assets/img/logo-stainaa.png') }}"
                            alt="Logo STAI Nurul Abror Al-Robbaniyin"
                            class="h-full w-full rounded-full object-contain"
                        >
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold tracking-tight text-white transition group-hover:text-blue-100">STAI Nurul Abror</p>
                        <p class="truncate text-[11px] font-medium text-blue-200">Portal Mahasiswa &amp; EDOM</p>
                    </div>
                </a>
                <button id="mobile-sidebar-close" type="button" class="rounded-lg p-1.5 text-blue-200 transition hover:bg-white/10 hover:text-white lg:hidden" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Kartu Identitas Mahasiswa --}}
            <div class="border-b border-white/10 px-4 py-4">
                <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 backdrop-blur-xs">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#2563EB] text-sm font-bold text-white shadow-xs">
                        {{ $inisialMahasiswa }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $namaMahasiswa }}</p>
                        <p class="truncate font-mono text-xs text-blue-200">{{ $nimMahasiswa }}</p>
                    </div>
                </div>
            </div>

            {{-- Navigasi Mahasiswa --}}
            <nav class="sidebar-scroll flex-1 space-y-6 overflow-y-auto px-4 py-5">
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-blue-200/70">Menu Utama</p>
                    <div class="space-y-1.5">
                        <a href="{{ route('mahasiswa.dashboard') }}" class="{{ $linkClass(request()->routeIs('mahasiswa.dashboard')) }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span>Beranda</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-blue-200/70">Pengisian Kuisioner</p>
                    <div class="space-y-1.5">
                        <a href="{{ route('mahasiswa.quisioner.index') }}" class="{{ $linkClass(request()->routeIs('mahasiswa.quisioner.*') && request()->query('type') === null) }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                            </svg>
                            <span>Semua Quisioner</span>
                        </a>
                        <a href="{{ route('mahasiswa.quisioner.index', ['type' => 'edom']) }}" class="{{ $linkClass(request()->routeIs('mahasiswa.quisioner.*') && request()->query('type') === 'edom') }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            <span>Kuisioner EDOM</span>
                        </a>
                        <a href="{{ route('mahasiswa.quisioner.index', ['type' => 'biasa']) }}" class="{{ $linkClass(request()->routeIs('mahasiswa.quisioner.*') && request()->query('type') === 'biasa') }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Kuisioner Umum</span>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Footer Sidebar (Logout dengan Konfirmasi SweetAlert2) --}}
            <div class="border-t border-white/10 p-4">
                <form method="POST" action="{{ route('logout') }}" data-confirm-logout>
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between rounded-xl border border-white/15 bg-white/10 px-3.5 py-2.5 text-xs font-semibold text-blue-100 transition hover:border-rose-400/50 hover:bg-rose-600 hover:text-white">
                        <span>Keluar dari Sistem</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Area Konten Utama Mahasiswa --}}
        <div class="flex min-h-screen flex-1 flex-col">
            {{-- Top Navbar Putih Bersih --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-blue-100 bg-white/95 px-4 shadow-2xs backdrop-blur-xs sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                    <button id="mobile-sidebar-open" type="button" class="rounded-xl border border-blue-100 bg-blue-50/50 p-2 text-[#0F294A] transition hover:bg-blue-100 lg:hidden" aria-label="Buka menu navigasi">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <a href="{{ route('mahasiswa.dashboard') }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white p-0.5 shadow-2xs ring-1 ring-blue-200 transition hover:scale-105 lg:hidden" title="STAI Nurul Abror Al-Robbaniyin">
                        <img
                            src="{{ asset('assets/img/logo-stainaa.png') }}"
                            alt="Logo STAINAA"
                            class="h-full w-full rounded-full object-contain"
                        >
                    </a>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-bold text-[#0F294A] sm:text-lg">@yield('page-title', 'Beranda Mahasiswa')</h1>
                        @hasSection('page-subtitle')
                            <p class="hidden truncate text-xs text-slate-500 sm:block">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2.5 sm:gap-3">
                    <span class="hidden items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-[#1E3A8A] md:inline-flex">
                        <span class="h-2 w-2 rounded-full bg-[#2563EB]"></span>
                        Portal Mahasiswa
                    </span>

                    {{-- Dropdown Profil Mahasiswa --}}
                    <div class="relative">
                        <button id="profile-menu-toggle" type="button" aria-expanded="false" class="flex items-center gap-2.5 rounded-xl border border-blue-100 bg-white px-2.5 py-1.5 text-left transition hover:border-[#3B82F6]/50 hover:bg-blue-50/40">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#0F294A] to-[#2563EB] text-xs font-bold text-white shadow-2xs">
                                {{ $inisialMahasiswa }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="max-w-[150px] truncate text-xs font-semibold text-[#0F294A]">{{ $namaMahasiswa }}</p>
                                <p class="font-mono text-[11px] text-slate-500">{{ $nimMahasiswa }}</p>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="profile-menu" class="absolute right-0 mt-2 hidden w-60 rounded-2xl border border-blue-100 bg-white p-1.5 shadow-xl">
                            <div class="border-b border-slate-100 px-3 py-2.5">
                                <p class="truncate text-xs font-bold text-[#0F294A]">{{ $namaMahasiswa }}</p>
                                <p class="truncate font-mono text-[11px] text-slate-500">NIM: {{ $nimMahasiswa }}</p>
                            </div>
                            <a href="{{ route('mahasiswa.dashboard') }}" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-[#2563EB]">
                                Beranda Mahasiswa
                            </a>
                            <a href="{{ route('mahasiswa.quisioner.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-blue-50 hover:text-[#2563EB]">
                                Daftar Quisioner
                            </a>
                            @if (Route::has('logout'))
                                <form method="POST" action="{{ route('logout') }}" data-confirm-logout class="mt-1 border-t border-slate-100 pt-1">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            {{-- Konten --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @hasSection('header-actions')
                    <div class="mb-5 flex flex-wrap items-center justify-end gap-2">
                        @yield('header-actions')
                    </div>
                @endif

                @include('layouts.partials.alerts')

                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-blue-100 bg-white px-4 py-3.5 text-center text-xs text-slate-500 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} STAI Nurul Abror Al-Robbaniyin &mdash; Portal Mahasiswa.
            </footer>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
