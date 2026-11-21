<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'mahasiswa' ? 'mahasiswa' : 'non-mahasiswa';
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $mahasiswaNims = Mahasiswa::query()
            ->pluck('nim')
            ->map(fn ($nim) => trim((string) $nim))
            ->filter(fn ($nim) => $nim !== '')
            ->unique()
            ->values()
            ->all();

        $totalMahasiswaSiakad = count($mahasiswaNims);

        $existingMahasiswaUsernames = empty($mahasiswaNims)
            ? []
            : User::query()
                ->whereIn('username', $mahasiswaNims)
                ->pluck('username')
                ->map(fn ($u) => trim((string) $u))
                ->unique()
                ->all();

        $totalAkunMahasiswa = count($existingMahasiswaUsernames);
        $belumPunyaAkun = max(0, $totalMahasiswaSiakad - $totalAkunMahasiswa);

        $totalNonMahasiswa = empty($mahasiswaNims)
            ? User::count()
            : User::whereNotIn('username', $mahasiswaNims)->count();

        $users = User::query()
            ->with('mahasiswa')
            ->when($tab === 'mahasiswa', function ($q) use ($mahasiswaNims) {
                $q->whereIn('username', empty($mahasiswaNims) ? ['__none__'] : $mahasiswaNims);
            })
            ->when($tab === 'non-mahasiswa' && ! empty($mahasiswaNims), function ($q) use ($mahasiswaNims) {
                $q->whereNotIn('username', $mahasiswaNims);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('username', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['0', '1'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'tab' => $tab,
            'users' => $users,
            'search' => $search,
            'status' => $status,
            'totalNonMahasiswa' => $totalNonMahasiswa,
            'totalAkunMahasiswa' => $totalAkunMahasiswa,
            'totalMahasiswaSiakad' => $totalMahasiswaSiakad,
            'belumPunyaAkun' => $belumPunyaAkun,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tb_users', 'username'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (Mahasiswa::where('nim', trim((string) $value))->exists()) {
                        $fail('Username tersebut adalah NIM mahasiswa. Gunakan tombol Generate Akun Otomatis pada Tab Mahasiswa.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:4', 'max:100', 'confirmed'],
            'status' => ['required', Rule::in(['0', '1'])],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username tersebut sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 4 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'status.required' => 'Status akun wajib dipilih.',
        ]);

        User::create([
            'username' => trim($validated['username']),
            'password' => $validated['password'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.users.index', ['tab' => 'non-mahasiswa'])
            ->with('success', 'Akun pengguna non-mahasiswa berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $user->load('mahasiswa');

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $isMahasiswa = $user->isMahasiswa();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('tb_users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:4', 'max:100', 'confirmed'],
            'status' => ['required', Rule::in(['0', '1'])],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username tersebut sudah digunakan akun lain.',
            'password.min' => 'Password baru minimal 4 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
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
            ->route('admin.users.index', ['tab' => $isMahasiswa ? 'mahasiswa' : 'non-mahasiswa'])
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Generate akun mahasiswa otomatis secara massal dari tb_mahasiswa ke tb_users
     * menggunakan chunking 500 baris di dalam DB::transaction() (anti-duplikat).
     */
    public function generateMahasiswaAccounts(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $allNims = Mahasiswa::query()
                ->pluck('nim')
                ->map(fn ($nim) => trim((string) $nim))
                ->filter(fn ($nim) => $nim !== '')
                ->unique()
                ->values();

            $existingUsernames = User::query()
                ->pluck('username')
                ->map(fn ($u) => trim((string) $u))
                ->flip();

            $nimsToCreate = $allNims->filter(fn ($nim) => ! $existingUsernames->has($nim))->values();
            $created = 0;
            $skipped = $allNims->count() - $nimsToCreate->count();

            if ($nimsToCreate->isNotEmpty()) {
                DB::transaction(function () use ($nimsToCreate, &$created) {
                    foreach ($nimsToCreate->chunk(500) as $chunk) {
                        $rows = [];
                        foreach ($chunk as $nim) {
                            $rows[] = [
                                'username' => $nim,
                                'password' => Hash::make($nim),
                                'status' => '1',
                            ];
                            $created++;
                        }
                        User::insert($rows);
                    }
                });
            }

            $pesan = $created > 0
                ? "Berhasil membuat {$created} akun mahasiswa baru (password default = NIM). {$skipped} mahasiswa sudah memiliki akun."
                : "Seluruh {$skipped} mahasiswa sudah memiliki akun di tb_users. Tidak ada akun baru yang perlu dibuat.";

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $pesan,
                    'data' => [
                        'total_mahasiswa' => $allNims->count(),
                        'created' => $created,
                        'skipped' => $skipped,
                    ],
                ]);
            }

            return redirect()
                ->route('admin.users.index', ['tab' => 'mahasiswa'])
                ->with('success', $pesan);
        } catch (Throwable $e) {
            $errorMsg = 'Gagal melakukan generate akun mahasiswa: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMsg,
                ], 500);
            }

            return redirect()
                ->route('admin.users.index', ['tab' => 'mahasiswa'])
                ->with('error', $errorMsg);
        }
    }

    /**
     * Reset password satu pengguna:
     * - Mahasiswa: otomatis direset kembali ke NIM masing-masing.
     * - Non-Mahasiswa: direset sesuai password baru yang diinputkan Admin.
     */
    public function resetPassword(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $isMahasiswa = $user->isMahasiswa();

        if ($isMahasiswa) {
            $newPassword = trim((string) $user->username);
            $user->update(['password' => $newPassword]);
            $pesan = "Password akun mahasiswa {$user->username} berhasil direset kembali ke NIM.";
        } else {
            $validated = $request->validate([
                'password' => ['required', 'string', 'min:4', 'max:100'],
            ], [
                'password.required' => 'Password baru wajib diisi.',
                'password.min' => 'Password baru minimal 4 karakter.',
            ]);

            $user->update(['password' => $validated['password']]);
            $pesan = "Password akun {$user->username} berhasil diperbarui.";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $pesan,
            ]);
        }

        return redirect()
            ->route('admin.users.index', ['tab' => $isMahasiswa ? 'mahasiswa' : 'non-mahasiswa'])
            ->with('success', $pesan);
    }

    /**
     * Reset password massal seluruh akun mahasiswa kembali ke NIM masing-masing (chunk 500).
     */
    public function resetPasswordMahasiswaMassal(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $mahasiswaNims = Mahasiswa::query()
                ->pluck('nim')
                ->map(fn ($nim) => trim((string) $nim))
                ->filter(fn ($nim) => $nim !== '')
                ->unique()
                ->values()
                ->all();

            $updated = 0;

            if (! empty($mahasiswaNims)) {
                DB::transaction(function () use ($mahasiswaNims, &$updated) {
                    User::query()
                        ->whereIn('username', $mahasiswaNims)
                        ->orderBy('id')
                        ->chunkById(500, function ($chunk) use (&$updated) {
                            foreach ($chunk as $user) {
                                $nim = trim((string) $user->username);
                                User::where('id', $user->id)->update([
                                    'password' => Hash::make($nim),
                                ]);
                                $updated++;
                            }
                        });
                });
            }

            $pesan = $updated > 0
                ? "Password untuk {$updated} akun mahasiswa berhasil direset kembali ke NIM masing-masing."
                : 'Belum ada akun mahasiswa di tb_users untuk direset.';

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $pesan,
                    'data' => ['updated' => $updated],
                ]);
            }

            return redirect()
                ->route('admin.users.index', ['tab' => 'mahasiswa'])
                ->with('success', $pesan);
        } catch (Throwable $e) {
            $errorMsg = 'Gagal mereset password massal mahasiswa: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMsg,
                ], 500);
            }

            return redirect()
                ->route('admin.users.index', ['tab' => 'mahasiswa'])
                ->with('error', $errorMsg);
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        $isMahasiswa = $user->isMahasiswa();

        if ((int) Auth::id() === (int) $user->id) {
            return redirect()
                ->route('admin.users.index', ['tab' => 'non-mahasiswa'])
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan untuk login.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index', ['tab' => $isMahasiswa ? 'mahasiswa' : 'non-mahasiswa'])
            ->with('success', 'Akun pengguna berhasil dihapus.');
    }
}
