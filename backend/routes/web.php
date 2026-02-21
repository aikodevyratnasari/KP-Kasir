<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Cashier\OrderController;
use App\Http\Controllers\Cashier\PaymentController;
use App\Http\Controllers\Cashier\ReceiptController;
use App\Http\Controllers\Cashier\TableController;
use App\Http\Controllers\Kitchen\KitchenDisplayController;
use App\Http\Controllers\Manager\CategoryController;
use App\Http\Controllers\Manager\ProductController;
use App\Http\Controllers\Manager\ReportController;
use Illuminate\Support\Facades\Route;

/*
|───────────────────────────────────────────────────────────────────────────────
|  AUTH ROUTES  (Breeze-style)
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post')
         ->middleware('login.throttle');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
     ->name('logout')
     ->middleware('auth');

/*
|───────────────────────────────────────────────────────────────────────────────
|  AUTHENTICATED ROUTES
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware(['auth', 'account.status', 'store.scope'])->group(function () {

    // Root redirect ke dashboard sesuai role
    Route::get('/', fn () => redirect(auth()->user()->dashboardRoute()))->name('home');

    // ── Profile (Breeze standard) ─────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',     [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',   [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    });

    // password.update alias agar view Breeze tidak error
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    /*──────────────────────────────────────────────────────
    |  ADMIN  /admin/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin')
         ->prefix('admin')->name('admin.')
         ->group(function () {

        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');

        Route::resource('users', UserController::class)->except(['destroy']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
             ->name('users.toggle-status');
    });

    /*──────────────────────────────────────────────────────
    |  MANAGER  /manager/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin,manager')
         ->prefix('manager')->name('manager.')
         ->group(function () {

        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');

        // Menu
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('products/trashed', [ProductController::class, 'trashed'])->name('products.trashed');
        Route::resource('products', ProductController::class)->except(['show']);
        Route::patch('products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');

        // Meja
        Route::resource('tables', \App\Http\Controllers\Manager\TableManagerController::class)->except(['show']);

        // Laporan
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales',    [ReportController::class, 'sales'])->name('sales');
            Route::get('/products', [ReportController::class, 'products'])->name('products');
            Route::get('/revenue',  [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/cashiers', [ReportController::class, 'cashiers'])->name('cashiers');
        });

        // Pembayaran (manager level)
        Route::get('payments/history',           [PaymentController::class, 'history'])->name('payments.history');
        Route::post('payments/{payment}/refund',  [PaymentController::class, 'refund'])->name('payments.refund');

        // Override cancel & transfer
        Route::post('orders/{order}/cancel',           [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('orders/{order}/transfer-table',   [TableController::class, 'transfer'])->name('orders.transfer-table');
    });

    /*──────────────────────────────────────────────────────
    |  CASHIER  /cashier/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin,manager,cashier')
         ->prefix('cashier')->name('cashier.')
         ->group(function () {

        // Orders
        Route::resource('orders', OrderController::class)->except(['destroy']);
        Route::post('orders/{order}/cancel',  [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        // Pembayaran
        Route::get('orders/{order}/payment',  [PaymentController::class, 'create'])->name('payments.create');
        Route::post('orders/{order}/payment', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/history',        [PaymentController::class, 'history'])->name('payments.history');

        // Struk
        Route::get('receipts/{payment}',       [ReceiptController::class, 'show'])->name('receipts.show');
        Route::get('receipts/{payment}/print', [ReceiptController::class, 'print'])->name('receipts.print');

        // Meja & Reservasi
        Route::get('tables',                        [TableController::class, 'index'])->name('tables.index');
        Route::get('reservations/create',            [TableController::class, 'createReservation'])->name('reservations.create');
        Route::post('reservations',                  [TableController::class, 'storeReservation'])->name('reservations.store');
        Route::delete('reservations/{reservation}',  [TableController::class, 'cancelReservation'])->name('reservations.cancel');
    });

    /*──────────────────────────────────────────────────────
    |  KITCHEN DISPLAY
    ──────────────────────────────────────────────────────*/
    Route::middleware('kitchen.access')
         ->prefix('kitchen')->name('kitchen.')
         ->group(function () {

        Route::get('/display',                      [KitchenDisplayController::class, 'index'])->name('display');
        Route::get('/poll',                         [KitchenDisplayController::class, 'poll'])->name('poll');
        Route::post('/orders/{kitchenOrder}/start', [KitchenDisplayController::class, 'start'])->name('orders.start');
        Route::post('/orders/{kitchenOrder}/ready', [KitchenDisplayController::class, 'ready'])->name('orders.ready');
    });
});
