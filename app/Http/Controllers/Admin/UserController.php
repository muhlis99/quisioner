<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $mahasiswaNims = Mahasiswa::pluck('nim')->all();

        $users = User::query()
            ->with('mahasiswa')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('username', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($role === 'mahasiswa', fn ($q) => $q->whereIn('username', empty($mahasiswaNims) ? ['__none__'] : $mahasiswaNims))
            ->when($role === 'admin' && ! empty($mahasiswaNims), fn ($q) => $q->whereNotIn('username', $mahasiswaNims))
            ->when(in_array($status, ['0', '1'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        $existingUsernames = User::pluck('username')->all();
        $mahasiswaTanpaAkun = Mahasiswa::query()
            ->when(! empty($existingUsernames), fn ($q) => $q->whereNotIn('nim', $existingUsernames))
            ->orderBy('nim')
            ->get();

        return view('admin.users.create', [
            'mahasiswaTanpaAkun' => $mahasiswaTanpaAkun,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('tb_users', 'username')],
            'password' => ['required', 'string', 'min:4', 'max:100'],
            'status' => ['required', Rule::in(['0', '1'])],
        ], [
            'username.required' => 'Username / NIM wajib diisi.',
            'username.unique' => 'Username / NIM tersebut sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 4 karakter.',
            'status.required' => 'Status akun wajib dipilih.',
        ]);

        User::create([
            'username' => trim($validated['username']),
            'password' => $validated['password'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $user->load('mahasiswa');

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('tb_users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'status' => ['required', Rule::in(['0', '1'])],
        ], [
            'username.required' => 'Username / NIM wajib diisi.',
            'username.unique' => 'Username / NIM tersebut sudah digunakan akun lain.',
            'password.min' => 'Password baru minimal 4 karakter.',
            'status.required' => 'Status akun wajib dipilih.',
        ]);

        $data = [
            'username' => trim($validated['username']),
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) Auth::id() === (int) $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan untuk login.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
