<!DOCTYPE html>
<html lang="id">
<head>
    @section('title', 'Masuk - Sistem Kuisioner & EDOM')
    @include('layouts.partials.head')
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-slate-800 antialiased">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="grid w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-950/40 lg:grid-cols-12">
            {{-- Panel Kiri: Identitas Sistem --}}
            <div class="relative flex flex-col justify-between bg-slate-900 p-8 text-white sm:p-10 lg:col-span-5">
                <div>
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-900/50">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>

                    <h1 class="mt-6 text-2xl font-bold tracking-tight">
                        {{ config('app.name', 'Sistem Kuisioner') }}
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">
                        Portal Evaluasi Dosen Oleh Mahasiswa (EDOM) dan pengisian kuisioner akademik terintegrasi SIAKAD.
                    </p>
                </div>

                <div class="mt-8 space-y-3 border-t border-slate-800 pt-6 text-xs text-slate-300">
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-400"></span>
                        <p><strong class="text-white">Mahasiswa:</strong> Masuk menggunakan <strong>NIM</strong> dan password akun Anda.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-400"></span>
                        <p><strong class="text-white">Admin:</strong> Masuk menggunakan <strong>Username</strong> administrator.</p>
                    </div>
                </div>
            </div>

            {{-- Panel Kanan: Form Login --}}
            <div class="p-8 sm:p-10 lg:col-span-7">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Masuk ke Akun Anda</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Sistem akan mengarahkan Anda otomatis ke Panel Admin atau Portal Mahasiswa.
                    </p>
                </div>

                @include('layouts.partials.alerts')

                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700">
                            Username / NIM
                        </label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </span>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="{{ old('username') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Masukkan Username atau NIM"
                                class="block w-full rounded-xl border @error('username') border-rose-400 bg-rose-50/40 @else border-slate-300 bg-white @enderror py-2.5 pr-4 pl-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                            >
                        </div>
                        @error('username')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">
                            Password
                        </label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan Password"
                                class="block w-full rounded-xl border @error('password') border-rose-400 bg-rose-50/40 @else border-slate-300 bg-white @enderror py-2.5 pr-12 pl-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                            >
                            <button
                                type="button"
                                id="toggle-password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600"
                                aria-label="Tampilkan atau sembunyikan password"
                            >
                                <svg id="eye-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg id="eye-closed" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/40 focus:outline-none"
                    >
                        <span>Masuk</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Sistem Kuisioner') }}. Hak cipta dilindungi.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            toggleBtn?.addEventListener('click', () => {
                if (!passwordInput) return;
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeOpen?.classList.toggle('hidden', isPassword);
                eyeClosed?.classList.toggle('hidden', !isPassword);
            });

            document.querySelectorAll('[data-close-alert]').forEach((btn) => {
                btn.addEventListener('click', () => btn.closest('[data-alert]')?.remove());
            });
        });
    </script>
</body>
</html>
