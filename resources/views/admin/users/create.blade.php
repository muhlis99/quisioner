@extends('layouts.admin')

@section('title', 'Tambah Pengguna - Admin')
@section('page-title', 'Tambah Pengguna')
@section('page-subtitle', 'Buat akun Admin (username bebas) atau Mahasiswa (gunakan NIM dari tb_mahasiswa)')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="username" class="block text-sm font-semibold text-slate-700">Username / NIM <span class="text-rose-500">*</span></label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    list="nim-mahasiswa-list"
                    value="{{ old('username') }}"
                    required
                    placeholder="Ketik username admin atau pilih NIM mahasiswa"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                <datalist id="nim-mahasiswa-list">
                    @foreach ($mahasiswaTanpaAkun as $mhs)
                        <option value="{{ $mhs->nim }}">{{ $mhs->nim }} - {{ $mhs->nama }}</option>
                    @endforeach
                </datalist>
                <p class="mt-1.5 text-xs text-slate-500">
                    Jika <strong>Username</strong> cocok dengan NIM di <code class="rounded bg-slate-100 px-1">tb_mahasiswa</code>, akun otomatis berperan sebagai <strong>Mahasiswa</strong>. Jika tidak, berperan sebagai <strong>Admin</strong>.
                </p>
                @error('username')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password <span class="text-rose-500">*</span></label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    placeholder="Minimal 4 karakter (otomatis di-hash bcrypt)"
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-slate-700">Status Akun <span class="text-rose-500">*</span></label>
                <select
                    id="status"
                    name="status"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                    <option value="1" @selected(old('status', '1') === '1')>Aktif (1)</option>
                    <option value="0" @selected(old('status') === '0')>Nonaktif (0)</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
@endsection
