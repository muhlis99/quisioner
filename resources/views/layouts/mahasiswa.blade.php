<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    @php
        $namaMahasiswa = session('mahasiswa_nama') ?? auth()->user()?->mahasiswa?->nama ?? auth()->user()?->username ?? 'Mahasiswa';
        $nimMahasiswa = session('mahasiswa_nim') ?? auth()->user()?->mahasiswa?->nim ?? auth()->user()?->username ?? '-';
        $inisialMahasiswa = strtoupper(mb_substr($namaMahasiswa, 0, 1));

        $linkClass = fn (bool $active): string => $active
            ? 'flex items-center gap-3 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition'
            : 'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700';
    @endphp

    <div class="min-h-screen lg:flex">
        {{-- Overlay Mobile --}}
        <div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-xs lg:hidden"></div>

        {{-- Sidebar Mahasiswa --}}
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-xl transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:shadow-none">
            {{-- Brand --}}
            <div class="flex h-16 items-center justify-between border-b border-slate-200 px-5">
                <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 font-bold text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold tracking-tight text-slate-900">Portal Mahasiswa</p>
                        <p class="text-[11px] text-slate-500">Pengisian Kuisioner &amp; EDOM</p>
                    </div>
                </a>
                <button id="mobile-sidebar-close" type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 lg:hidden" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Kartu Identitas Singkat Mahasiswa --}}
            <div class="border-b border-slate-100 px-4 py-4">
                <div class="flex items-center gap-3 rounded-2xl bg-emerald-50/70 p-3 ring-1 ring-emerald-100">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-sm font-bold text-white">
                        {{ $inisialMahasiswa }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $namaMahasiswa }}</p>
                        <p class="truncate text-xs text-emerald-700">{{ $nimMahasiswa }}</p>
                    </div>
                </div>
            </div>

            {{-- Navigasi Mahasiswa --}}
            <nav class="sidebar-scroll flex-1 space-y-6 overflow-y-auto px-4 py-5">
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Menu Utama</p>
                    <div class="space-y-1">
                        <a href="{{ route('mahasiswa.dashboard') }}" class="{{ $linkClass(request()->routeIs('mahasiswa.dashboard')) }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span>Beranda</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Pengisian Kuisioner</p>
                    <div class="space-y-1">
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

                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Akademik &amp; Status</p>
                    <div class="space-y-1">
                        <a href="{{ Route::has('mahasiswa.makul.index') ? route('mahasiswa.makul.index') : '#' }}" class="{{ $linkClass(request()->routeIs('mahasiswa.makul.*')) }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span>Mata Kuliah Diambil</span>
                        </a>
                        <a href="{{ Route::has('mahasiswa.ketuntasan.index') ? route('mahasiswa.ketuntasan.index') : '#' }}" class="{{ $linkClass(request()->routeIs('mahasiswa.ketuntasan.*')) }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Status Ketuntasan</span>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Footer Sidebar --}}
            <div class="border-t border-slate-200 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-medium text-rose-700 transition hover:bg-rose-600 hover:text-white">
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
            {{-- Navbar Atas --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur-xs sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button id="mobile-sidebar-open" type="button" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu navigasi">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base font-bold text-slate-900 sm:text-lg">@yield('page-title', 'Beranda Mahasiswa')</h1>
                        @hasSection('page-subtitle')
                            <p class="hidden text-xs text-slate-500 sm:block">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 md:inline-flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Portal Mahasiswa
                    </span>

                    {{-- Dropdown Profil Mahasiswa --}}
                    <div class="relative">
                        <button id="profile-menu-toggle" type="button" aria-expanded="false" class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-left transition hover:bg-slate-50">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-xs font-bold text-white">
                                {{ $inisialMahasiswa }}
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-xs font-semibold text-slate-800">{{ $namaMahasiswa }}</p>
                                <p class="text-[11px] text-slate-500">{{ $nimMahasiswa }}</p>
                            </div>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div id="profile-menu" class="absolute right-0 mt-2 hidden w-56 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                            <div class="border-b border-slate-100 px-3 py-2 sm:hidden">
                                <p class="text-xs font-semibold text-slate-800">{{ $namaMahasiswa }}</p>
                                <p class="truncate text-[11px] text-slate-500">{{ $nimMahasiswa }}</p>
                            </div>
                            <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                Beranda Mahasiswa
                            </a>
                            @if (Route::has('logout'))
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50">
                                        Keluar
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
                    <div class="mb-6 flex flex-wrap items-center justify-end gap-2">
                        @yield('header-actions')
                    </div>
                @endif

                @include('layouts.partials.alerts')

                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-slate-200 bg-white px-4 py-3.5 text-center text-xs text-slate-500 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} {{ config('app.name', 'Sistem Kuisioner') }} &mdash; Portal Mahasiswa.
            </footer>
        </div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
