<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Memastikan pengguna yang login memiliki role yang sesuai ('admin' atau 'mahasiswa').
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('warning', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = $request->user();
        $currentRole = $request->session()->get('user_role');

        if (! $currentRole) {
            $mahasiswa = $user->mahasiswa;
            $currentRole = $mahasiswa ? 'mahasiswa' : 'admin';
            $request->session()->put('user_role', $currentRole);

            if ($mahasiswa) {
                $request->session()->put([
                    'mahasiswa_nim' => $mahasiswa->nim,
                    'mahasiswa_nama' => $mahasiswa->nama,
                ]);
            }
        }

        if ($currentRole !== $role) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }

            $targetRoute = $currentRole === 'mahasiswa'
                ? 'mahasiswa.dashboard'
                : 'admin.dashboard';

            return redirect()->route($targetRoute)
                ->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
