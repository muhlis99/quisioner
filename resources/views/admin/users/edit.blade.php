@extends('layouts.admin')

@section('title', 'Edit Pengguna - Admin')
@section('page-title', 'Edit Pengguna')
@section('page-subtitle', 'Perbarui username, password, atau status akun pengguna')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="username" class="block text-sm font-semibold text-slate-700">Username / NIM <span class="text-rose-500">*</span></label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username', $user->username) }}"
                    required
                    class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
                @if ($user->mahasiswa)
                    <p class="mt-1.5 text-xs text-emerald-700">Terhubung ke Mahasiswa: <strong>{{ $user->mahasiswa->nama }}</strong></p>
                @endif
                @error('username')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password Baru <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Kosongkan jika tidak ingin mengubah password"
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
                    <option value="1" @selected(old('status', $user->status) === '1')>Aktif (1)</option>
                    <option value="0" @selected(old('status', $user->status) === '0')>Nonaktif (0)</option>
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
                    Perbarui Pengguna
                </button>
            </div>
        </form>
    </div>
@endsection
