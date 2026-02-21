<?php

use App\Http\Middleware\ActivityLogger;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\KitchenOnly;
use App\Http\Middleware\LoginThrottle;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\StoreScope;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Global middleware ──────────────────────────────────────────
        // ActivityLogger mencatat semua mutasi (POST/PUT/PATCH/DELETE)
        $middleware->append(ActivityLogger::class);

        // ── Middleware aliases ─────────────────────────────────────────
        $middleware->alias([
            // Breeze standard
            'auth'           => Authenticate::class,
            'guest'          => RedirectIfAuthenticated::class,

            // DePOS custom
            'role'           => RoleMiddleware::class,
            'account.status' => CheckAccountStatus::class,
            'store.scope'    => StoreScope::class,
            'kitchen.access' => KitchenOnly::class,
            'login.throttle' => LoginThrottle::class,
            'force.json'     => ForceJsonResponse::class,
        ]);

        // ── Web group (Breeze default, tidak perlu diubah) ─────────────
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ── API group ──────────────────────────────────────────────────
        $middleware->group('api', [
            ForceJsonResponse::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
