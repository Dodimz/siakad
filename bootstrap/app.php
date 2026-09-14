<?php

use App\Http\Middleware\EnsureKampus;
use App\Http\Middleware\EnsurePmbToken;
use App\Http\Middleware\FakultasScopeMiddleware;
use App\Http\Middleware\RequestLoggingMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SetDefaultKampus;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Configure rate limiters
            RateLimiter::for('krs', function (Request $request) {
                return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
            });

            RateLimiter::for('penilaian', function (Request $request) {
                return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
            });

            RateLimiter::for('sensitive', function (Request $request) {
                return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
            });

            RateLimiter::for('ai-chat', function (Request $request) {
                return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - applies to all requests
        $middleware->append(SecurityHeadersMiddleware::class);

        // Middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'log.requests' => RequestLoggingMiddleware::class,
            'fakultas.scope' => FakultasScopeMiddleware::class,
            'tenant' => EnsureKampus::class,
            'set.default.kampus' => SetDefaultKampus::class,
            'auth.pmb' => EnsurePmbToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
