<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login sebagai halaman utama.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi Admin (username) maupun Mahasiswa (NIM terhubung ke tb_mahasiswa).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau NIM wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = trim($credentials['username']);
        $user = User::with('mahasiswa')->where('username', $username)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['username' => 'Username / NIM atau password yang Anda masukkan salah.'])
                ->onlyInput('username');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $mahasiswa = $user->mahasiswa;

        if ($mahasiswa !== null) {
            $request->session()->put([
                'user_role' => 'mahasiswa',
                'mahasiswa_nim' => $mahasiswa->nim,
                'mahasiswa_nama' => $mahasiswa->nama,
            ]);

            return $this->safeRedirect($request, 'mahasiswa', route('mahasiswa.dashboard'))
                ->with('success', 'Selamat datang, ' . $mahasiswa->nama . '.');
        }

        $request->session()->put('user_role', 'admin');
        $request->session()->forget(['mahasiswa_nim', 'mahasiswa_nama']);

        return $this->safeRedirect($request, 'admin', route('admin.dashboard'))
            ->with('success', 'Selamat datang di Panel Administrator.');
    }

    /**
     * Redirect ke intended URL hanya jika sesuai dengan prefix role pengguna.
     */
    private function safeRedirect(Request $request, string $rolePrefix, string $fallbackUrl): RedirectResponse
    {
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && str_starts_with(parse_url($intended, PHP_URL_PATH) ?? '', '/' . $rolePrefix)) {
            return redirect()->to($intended);
        }

        return redirect()->to($fallbackUrl);
    }

    /**
     * Mengakhiri sesi login pengguna.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Mengarahkan pengguna yang sudah login ke dashboard sesuai role-nya.
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        $role = session('user_role') ?? $user->role;

        return $role === 'mahasiswa'
            ? redirect()->route('mahasiswa.dashboard')
            : redirect()->route('admin.dashboard');
    }
}
