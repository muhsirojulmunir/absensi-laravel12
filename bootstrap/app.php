<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/sync-users',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Kalau sesi/CSRF token sudah kedaluwarsa (mis. karena user menekan tombol
        // "kembali" di browser ke halaman lama), jangan tampilkan halaman error
        // "419 Page Expired" — cukup arahkan balik dengan pesan yang jelas.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()
                ->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('error', 'Sesi Anda sudah kedaluwarsa. Silakan coba lagi.');
        });
    })->create();
