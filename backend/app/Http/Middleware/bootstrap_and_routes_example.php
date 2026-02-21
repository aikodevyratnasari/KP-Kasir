<?php

/**
 * ============================================================
 *  bootstrap/app.php — Laravel 11 style
 *  Register all custom middleware aliases here
 * ============================================================
 */

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

        // ── Global middleware (runs on every request) ──────────────────
        $middleware->append(ActivityLogger::class);

        // ── Named / aliased middleware ─────────────────────────────────
        $middleware->alias([
            'auth'           => Authenticate::class,
            'guest'          => RedirectIfAuthenticated::class,
            'role'           => RoleMiddleware::class,
            'account.status' => CheckAccountStatus::class,
            'store.scope'    => StoreScope::class,
            'kitchen.access' => KitchenOnly::class,
            'login.throttle' => LoginThrottle::class,
            'force.json'     => ForceJsonResponse::class,
        ]);

        // ── Middleware groups ──────────────────────────────────────────
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            ForceJsonResponse::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ── Auth middleware chain (applied to all protected routes) ────
        // Tip: combine into a single group in routes:
        //   Route::middleware(['auth', 'account.status', 'store.scope'])
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();


/* ================================================================
   routes/web.php — Example route structure with middleware applied
   ================================================================ */

/*
use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('login.throttle');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Authenticated routes (all roles) ──────────────────────────────
Route::middleware(['auth', 'account.status', 'store.scope'])->group(function () {

    // ── Admin only ─────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('stores', StoreController::class);
        Route::resource('report-schedules', ReportScheduleController::class);
    });

    // ── Admin + Manager ────────────────────────────────────────────
    Route::middleware('role:admin,manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('tables', TableController::class);
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/cashiers', [ReportController::class, 'cashiers'])->name('reports.cashiers');
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/tables/{order}/transfer', [TableController::class, 'transfer'])->name('tables.transfer');
    });

    // ── Cashier + Manager + Admin ──────────────────────────────────
    Route::middleware('role:admin,manager,cashier')->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/receipts/{payment}', [ReceiptController::class, 'show'])->name('receipts.show');
        Route::get('/tables', [TableController::class, 'status'])->name('tables.status');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    });

    // ── Kitchen Display ────────────────────────────────────────────
    Route::middleware('kitchen.access')->prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/display', [KitchenDisplayController::class, 'index'])->name('display');
        Route::post('/orders/{kitchenOrder}/start', [KitchenDisplayController::class, 'start'])->name('orders.start');
        Route::post('/orders/{kitchenOrder}/ready', [KitchenDisplayController::class, 'ready'])->name('orders.ready');
    });

});
*/
