<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-[#F4F7F5] text-slate-800 antialiased">
    @php
        $adminName = auth()->user()->username ?? 'Administrator';
        $adminSubtitle = 'Administrator';
        $adminInitial = strtoupper(mb_substr($adminName, 0, 1));

        $linkClass = fn (bool $active): string => $active
            ? 'group flex items-center gap-3 rounded-r-xl rounded-l-xs border-l-4 border-[#20A464] bg-[#20A464]/18 pl-3 pr-3.5 py-2.5 text-sm font-semibold text-white shadow-xs transition'
            : 'group flex items-center gap-3 rounded-r-xl rounded-l-xs border-l-4 border-transparent pl-3 pr-3.5 py-2.5 text-sm font-medium text-white/75 transition hover:border-[#20A464]/50 hover:bg-white/6 hover:text-white';

        $iconClass = fn (bool $active): string => $active
            ? 'h-5 w-5 shrink-0 text-[#20A464]'
            : 'h-5 w-5 shrink-0 text-emerald-200/70 transition group-hover:text-white';
    @endphp

    <div class="min-h-screen lg:flex">
        {{-- Overlay Mobile --}}
        <div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-[#062419]/75 backdrop-blur-xs lg:hidden print:hidden"></div>

        {{-- Sidebar Admin (#062419 Dark Emerald) --}}
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-gradient-to-b from-[#062419] via-[#062419] to-[#083523] text-white shadow-2xl transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:shadow-none print:hidden">
            {{-- Header Logo & Nama Aplikasi --}}
            <div class="flex h-18 items-center justify-between border-b border-[#0B6B3A]/60 px-5">
                <a
                    href="{{ route('admin.dashboard') }}"
                    title="Sistem Kuisioner / STAINAA — STAI Nurul Abror Al-Robbaniyin"
                    class="group flex min-w-0 items-center gap-3"
                >
                    <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/10 p-1 ring-2 ring-emerald-400/30 shadow-sm transition duration-300 group-hover:scale-105 group-hover:ring-amber-300/60 group-hover:shadow-[0_0_18px_rgba(32,164,100,0.55)]">
                        <img
                            src="{{ asset('assets/img/logo-stainaa.png') }}"
                            alt="Logo STAI Nurul Abror Al-Robbaniyin"
                            class="h-full w-full rounded-full object-contain transition-transform duration-300"
                        >
                    </div>
                    <div class="min-w-0 transition-opacity duration-200">
                        <p class="truncate text-sm font-bold tracking-tight text-white transition group-hover:text-emerald-100">Sistem Kuisioner STAINAA</p>
                        <p class="truncate text-[11px] font-medium text-[#20A464]">Panel Admin &amp; EDOM</p>
                    </div>
                </a>
                <button id="mobile-sidebar-close" type="button" class="rounded-lg p-1.5 text-white/70 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Navigasi Admin (4 Kelompok Menu) --}}
            <nav class="sidebar-scroll flex-1 space-y-6 overflow-y-auto px-3.5 py-5">
                {{-- 1. Utama --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-200/55">Utama</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="{{ $linkClass(request()->routeIs('admin.dashboard')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.dashboard')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>

                {{-- 2. Master Data --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-200/55">Master Data</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="{{ $linkClass(request()->routeIs('admin.mahasiswa.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.mahasiswa.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                            </svg>
                            <span>Data Mahasiswa</span>
                        </a>
                        <a href="{{ route('admin.dosen.index') }}" class="{{ $linkClass(request()->routeIs('admin.dosen.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.dosen.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span>Data Dosen</span>
                        </a>
                        <a href="{{ route('admin.jatah-makul.index') }}" class="{{ $linkClass(request()->routeIs('admin.jatah-makul.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.jatah-makul.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span>Jatah Mata Kuliah</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="{{ $linkClass(request()->routeIs('admin.users.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.users.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <span>Kelola Pengguna</span>
                        </a>
                    </div>
                </div>

                {{-- 3. Manajemen Quisioner --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-200/55">Manajemen Quisioner</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.skor.index') }}" class="{{ $linkClass(request()->routeIs('admin.skor.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.skor.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                            <span>Skor Jawaban</span>
                        </a>
                        <a href="{{ route('admin.quisioner.index') }}" class="{{ $linkClass(request()->routeIs('admin.quisioner.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.quisioner.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Data Quisioner</span>
                        </a>
                    </div>
                </div>

                {{-- 4. Laporan --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-200/55">Laporan</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.laporan.index') }}" class="{{ $linkClass(request()->routeIs('admin.laporan.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.laporan.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            <span>Hasil Laporan</span>
                        </a>
                        <a href="{{ route('admin.ketuntasan.index') }}" class="{{ $linkClass(request()->routeIs('admin.ketuntasan.*')) }}">
                            <svg class="{{ $iconClass(request()->routeIs('admin.ketuntasan.*')) }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Monitoring Ketuntasan</span>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Footer Sidebar --}}
            <div class="border-t border-[#0B6B3A]/60 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between rounded-xl border border-rose-400/25 bg-rose-500/10 px-3.5 py-2.5 text-xs font-semibold text-rose-200 transition hover:bg-rose-600 hover:text-white">
                        <span>Keluar dari Sistem</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Area Konten Utama Admin --}}
        <div class="flex min-h-screen flex-1 flex-col">
            {{-- Top Navbar Putih Bersih --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-emerald-100 bg-white/95 px-4 shadow-2xs backdrop-blur-xs sm:px-6 lg:px-8 print:hidden">
                <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                    <button id="mobile-sidebar-open" type="button" class="rounded-xl border border-emerald-100 bg-[#F4F7F5] p-2 text-[#062419] transition hover:bg-emerald-50 lg:hidden" aria-label="Buka menu navigasi">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 p-0.5 ring-1 ring-emerald-200 transition hover:scale-105 lg:hidden" title="STAI Nurul Abror Al-Robbaniyin">
                        <img
                            src="{{ asset('assets/img/logo-stainaa.png') }}"
                            alt="Logo STAINAA"
                            class="h-full w-full rounded-full object-contain"
                        >
                    </a>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-bold tracking-tight text-[#062419] sm:text-lg">@yield('page-title', 'Dashboard Admin')</h1>
                        @hasSection('page-subtitle')
                            <p class="hidden truncate text-xs text-slate-500 sm:block">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3">
                    {{-- Dropdown Profil Admin --}}
                    <div class="relative">
                        <button id="profile-menu-toggle" type="button" aria-expanded="false" class="flex items-center gap-2.5 rounded-xl border border-emerald-100 bg-white px-2.5 py-1.5 text-left transition hover:border-[#20A464]/50 hover:bg-emerald-50/50">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B6B3A] text-xs font-bold text-white">
                                {{ $adminInitial }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-xs font-semibold text-[#062419]">{{ $adminName }}</p>
                                <p class="text-[11px] text-emerald-700">{{ $adminSubtitle }}</p>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="profile-menu" class="absolute right-0 mt-2 hidden w-56 rounded-xl border border-emerald-100 bg-white p-1.5 shadow-lg">
                            <div class="border-b border-emerald-50 px-3 py-2 sm:hidden">
                                <p class="text-xs font-semibold text-[#062419]">{{ $adminName }}</p>
                                <p class="truncate text-[11px] text-emerald-700">{{ $adminSubtitle }}</p>
                            </div>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-[#0B6B3A]">
                                Dashboard Admin
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-emerald-50 pt-1">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Tombol Logout Cepat di Top Navbar --}}
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-600 hover:text-white" title="Keluar dari sistem">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Konten Utama --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8 print:p-0">
                @hasSection('header-actions')
                    <div class="mb-5 flex flex-wrap items-center justify-end gap-2.5 print:hidden">
                        @yield('header-actions')
                    </div>
                @endif

                <div class="print:hidden">
                    @include('layouts.partials.alerts')
                </div>

                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-emerald-100 bg-white px-4 py-3.5 text-center text-xs text-slate-500 sm:px-6 lg:px-8 print:hidden">
                &copy; {{ date('Y') }} STAI Nurul Abror Al-Robbaniyin &mdash; Panel Administrator Sistem Kuisioner &amp; EDOM.
            </footer>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
