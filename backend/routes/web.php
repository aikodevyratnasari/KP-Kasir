<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
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
|  PUBLIC ROUTES
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('login.throttle');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|───────────────────────────────────────────────────────────────────────────────
|  EMAIL VERIFICATION ROUTES
|  - Harus auth tapi TIDAK harus verified (jangan pakai middleware 'verified' di sini)
|  - Throttle resend: Laravel built-in via 6,1 (6 kali per 1 menit)
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware(['auth', 'account.status'])->group(function () {
    // Halaman "silakan verifikasi email Anda"
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    // Proses klik link dari email (signed URL)
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    // Kirim ulang email verifikasi
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Profile (tidak perlu verified — user perlu bisa ubah profil meski belum verified)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password',  [ProfileController::class, 'updatePassword'])->name('password.update');
});

/*
|───────────────────────────────────────────────────────────────────────────────
|  AUTHENTICATED + VERIFIED ROUTES
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware(['auth', 'verified', 'account.status', 'store.scope'])->group(function () {

    Route::get('/', fn() => redirect(auth()->user()->dashboardRoute()));

    /*──────────────────────────────────────────────────────
    |  ADMIN — /admin/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');

            
            // User management
            Route::resource('users', UserController::class)->except(['destroy']);
            Route::patch('users/{user}/toggle-status',          [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::post('users/{user}/resend-verification',     [UserController::class, 'resendVerification'])->name('users.resend-verification');
        });

    /*──────────────────────────────────────────────────────
    |  MANAGER — /manager/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin,manager')
        ->prefix('manager')
        ->name('manager.')
        ->group(function () {

            Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');

           

            // Menu
            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::resource('products',   ProductController::class)->except(['show']);
            Route::patch('products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');
            Route::get('products/trashed',           [ProductController::class, 'trashed'])->name('products.trashed');

            // Table management
            Route::resource('tables', \App\Http\Controllers\Manager\TableManagerController::class)->except(['show']);
            
             Route::get('/dashboard/filter', [DashboardController::class, 'filter'])->name('dashboard.filter');

            // Reports
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/sales',    [ReportController::class, 'sales'])->name('sales');
                Route::get('/products', [ReportController::class, 'products'])->name('products');
                Route::get('/revenue',  [ReportController::class, 'revenue'])->name('revenue');
                Route::get('/cashiers', [ReportController::class, 'cashiers'])->name('cashiers');
            });

            // Payments
            Route::get('payments/history',           [PaymentController::class, 'history'])->name('payments.history');
            Route::post('payments/{payment}/refund',  [PaymentController::class, 'refund'])->name('payments.refund');

            // Order management
            Route::post('orders/{order}/cancel',         [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('orders/{order}/transfer-table', [TableController::class, 'transfer'])->name('orders.transfer-table');

                        // Variants
            Route::post('products/{product}/variants',             [ProductController::class, 'storeVariant'])->name('products.variants.store');
            Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

            // Discounts
            Route::post('products/{product}/discounts',                        [ProductController::class, 'storeDiscount'])->name('products.discounts.store');
            Route::delete('products/{product}/discounts/{discount}',           [ProductController::class, 'destroyDiscount'])->name('products.discounts.destroy');
            Route::patch('products/{product}/discounts/{discount}/toggle',     [ProductController::class, 'toggleDiscount'])->name('products.discounts.toggle');

            // Bundle Packages
            Route::get('bundles',                 [ProductController::class, 'bundles'])->name('bundles.index');
            Route::post('bundles',                [ProductController::class, 'storeBundle'])->name('bundles.store');
            Route::get('bundles/{bundle}/edit',   [ProductController::class, 'editBundle'])->name('bundles.edit');
            Route::put('bundles/{bundle}',        [ProductController::class, 'updateBundle'])->name('bundles.update');
            Route::delete('bundles/{bundle}',     [ProductController::class, 'destroyBundle'])->name('bundles.destroy');
        });



    /*──────────────────────────────────────────────────────
    |  CASHIER — /cashier/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin,manager,cashier')
        ->prefix('cashier')
        ->name('cashier.')
        ->group(function () {

            Route::resource('orders', OrderController::class)->except(['destroy']);
            Route::post('orders/{order}/cancel',    [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::patch('orders/{order}/status',   [OrderController::class, 'updateStatus'])->name('orders.status');

            Route::get('orders/{order}/payment',    [PaymentController::class, 'create'])->name('payments.create');
            Route::post('orders/{order}/payment',   [PaymentController::class, 'store'])->name('payments.store');
            Route::get('payments/history',          [PaymentController::class, 'history'])->name('payments.history');

            Route::get('receipts/{payment}',        [ReceiptController::class, 'show'])->name('receipts.show');
            Route::get('receipts/{payment}/print',  [ReceiptController::class, 'print'])->name('receipts.print');

            Route::get('tables',                    [TableController::class, 'index'])->name('tables.index');

            Route::get('reservations/create',           [TableController::class, 'createReservation'])->name('reservations.create');
            Route::post('reservations',                 [TableController::class, 'storeReservation'])->name('reservations.store');
            Route::delete('reservations/{reservation}', [TableController::class, 'cancelReservation'])->name('reservations.cancel');
        });

    /*──────────────────────────────────────────────────────
    |  KITCHEN DISPLAY
    ──────────────────────────────────────────────────────*/
    Route::middleware('kitchen.access')
        ->prefix('kitchen')
        ->name('kitchen.')
        ->group(function () {
            Route::get('/display',                      [KitchenDisplayController::class, 'index'])->name('display');
            Route::get('/poll',                         [KitchenDisplayController::class, 'poll'])->name('poll');
            Route::post('/orders/{kitchenOrder}/start', [KitchenDisplayController::class, 'start'])->name('orders.start');
            Route::post('/orders/{kitchenOrder}/ready', [KitchenDisplayController::class, 'ready'])->name('orders.ready');
        });
});