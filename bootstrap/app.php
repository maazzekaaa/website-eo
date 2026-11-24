<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->alias(['admin.protect' => \App\Http\Middleware\ProtectAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tamu yang membuka halaman admin (selain /admin/login) mendapat 404, bukan dialihkan ke login,
        // agar keberadaan panel admin tidak terlihat oleh orang tanpa akses.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('admin', 'admin/*') && ! $request->expectsJson()) {
                return response()->view('errors.404', [], 404);
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
