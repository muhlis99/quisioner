@extends('layouts.admin')

@section('title', 'Kelola Pengguna - Admin')
@section('page-title', 'Kelola Pengguna')
@section('page-subtitle', 'Manajemen akun Admin dan Mahasiswa pada tabel tb_users')

@section('header-actions')
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span>Tambah Pengguna</span>
    </a>
@endsection

@section('content')
    <div class="space-y-5">
        {{-- Filter & Pencarian --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-5">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari username, NIM, atau nama mahasiswa..."
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                    >
                </div>
                <div class="md:col-span-2">
                    <select name="role" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Role</option>
                        <option value="admin" @selected($role === 'admin')>Admin</option>
                        <option value="mahasiswa" @selected($role === 'mahasiswa')>Mahasiswa</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <select name="status" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($status === '1')>Aktif (1)</option>
                        <option value="0" @selected($status === '0')>Nonaktif (0)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 md:col-span-3 md:justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Filter
                    </button>
                    @if ($search !== '' || $role !== '' || $status !== '')
                        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Pengguna --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-14 px-5 py-3.5">No</th>
                            <th class="w-48 px-5 py-3.5">Username / NIM</th>
                            <th class="px-5 py-3.5">Terhubung ke SIAKAD</th>
                            <th class="w-32 px-5 py-3.5 text-center">Role</th>
                            <th class="w-28 px-5 py-3.5 text-center">Status</th>
                            <th class="w-44 px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $index => $user)
                            @php $isMhs = $user->mahasiswa !== null; @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-3.5 text-slate-500">{{ $users->firstItem() + $index }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-slate-900">
                                    {{ $user->username }}
                                    @if ((int) auth()->id() === (int) $user->id)
                                        <span class="ml-1.5 rounded bg-indigo-50 px-1.5 py-0.5 font-sans text-[10px] font-bold text-indigo-700">Anda</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-700">
                                    @if ($isMhs)
                                        <span class="font-medium text-slate-900">{{ $user->mahasiswa->nama }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">Akun Administrator</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($isMhs)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            Mahasiswa
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                                            Admin
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($user->status === '1')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            Edit
                                        </a>
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
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data pengguna yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-3.5">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
