<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));

        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();
            $role = $request->session()->get('user_role') ?? $user?->role;

            return $role === 'mahasiswa'
                ? route('mahasiswa.dashboard')
                : route('admin.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
