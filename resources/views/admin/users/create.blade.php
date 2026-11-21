@extends('layouts.admin')

@section('title', 'Tambah Pengguna Non-Mahasiswa - Admin')
@section('page-title', 'Tambah Pengguna Non-Mahasiswa')
@section('page-subtitle', 'Buat akun Administrator baru secara manual')

@section('content')
    <div class="mx-auto max-w-2xl overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm">
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-[#062419]">Formulir Pengguna Non-Mahasiswa (Admin)</h2>
            <p class="mt-0.5 text-xs text-slate-500">Untuk akun Mahasiswa, gunakan fitur Generate Akun Otomatis pada Tab Mahasiswa.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 p-6 sm:p-8">
            @csrf

            <div>
                <label for="username" class="block text-sm font-semibold text-slate-700">Username <span class="text-rose-500">*</span></label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    required
                    placeholder="Contoh: admin_prodi atau operator1"
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                @error('username')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password <span class="text-rose-500">*</span></label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        placeholder="Minimal 4 karakter"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi Password <span class="text-rose-500">*</span></label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        placeholder="Ulangi password"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                    >
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-slate-700">Status Akun <span class="text-rose-500">*</span></label>
                <select
                    id="status"
                    name="status"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition focus:border-[#20A464] focus:ring-2 focus:ring-[#20A464]/25 focus:outline-none"
                >
                    <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                    <option value="0" @selected(old('status') === '0')>Nonaktif</option>
                </select>
                @error('status')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-emerald-100 pt-5">
                <a href="{{ route('admin.users.index', ['tab' => 'non-mahasiswa']) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    &larr; Kembali
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#20A464] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#17804D]">
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </form>
    </div>
@endsection
