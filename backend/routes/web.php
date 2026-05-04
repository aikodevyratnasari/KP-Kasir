<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Cashier\OrderController;
use App\Http\Controllers\Cashier\PaymentController;
use App\Http\Controllers\Cashier\ReceiptController;
use App\Http\Controllers\Cashier\TableController;
use App\Http\Controllers\Kitchen\KitchenDisplayController;
use App\Http\Controllers\Manager\CategoryController;
use App\Http\Controllers\Manager\ProductController;
use App\Http\Controllers\Manager\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|───────────────────────────────────────────────────────────────────────────────
|  PUBLIC ROUTES
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('login.throttle');

    Route::get('/forgot-password',        [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password',       [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password',        [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|───────────────────────────────────────────────────────────────────────────────
|  EMAIL VERIFICATION ROUTES
|───────────────────────────────────────────────────────────────────────────────
*/
Route::get('/email/verify/{id}/{hash}',
    [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware(['auth', 'account.status'])->group(function () {
    Route::get('/email/verify',
        [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email/verification-notification',
        [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',        [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/password',         [ProfileController::class, 'updatePassword'])->name('password.update');
});

/*
|───────────────────────────────────────────────────────────────────────────────
|  AUTHENTICATED + VERIFIED ROUTES
|───────────────────────────────────────────────────────────────────────────────
*/
Route::middleware(['auth', 'verified', 'account.status', 'store.scope'])->group(function () {

    Route::get('/', fn() => redirect(auth()->user()->dashboardRoute()));

    // ── Polling API ──────────────────────────────────────────────────────
    Route::prefix('api/poll')->name('poll.')->group(function () {
        Route::get('tables',         [PollController::class, 'tables'])->name('tables');
        Route::get('orders',         [PollController::class, 'orders'])->name('orders');
        Route::get('orders/{order}', [PollController::class, 'order'])->name('order');
        Route::get('kitchen',        [PollController::class, 'kitchen'])->name('kitchen');
    });

    /*──────────────────────────────────────────────────────
    |  ADMIN — /admin/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard',        [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/dashboard/filter', [ReportController::class, 'dashboardFilter'])->name('dashboard.filter');

            // ── User Management ───────────────────────────────────────────
            Route::resource('users', UserController::class);
            Route::patch('users/{user}/toggle-status',      [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::post('users/{user}/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');
            Route::get('users/{user}/reset-password',       [UserController::class, 'showResetPassword'])->name('users.reset-password');
            Route::patch('users/{user}/reset-password',     [UserController::class, 'resetPassword'])->name('users.reset-password.update');

            // ── Store Management ──────────────────────────────────────────
            Route::resource('stores', StoreController::class)->except(['show', 'destroy']);
            Route::patch('stores/{store}/toggle-status',    [StoreController::class, 'toggleStatus'])->name('stores.toggle-status');
            Route::patch('stores/{store}/set-headquarters', [StoreController::class, 'setHeadquarters'])->name('stores.set-headquarters');
        });

    /*──────────────────────────────────────────────────────
    |  MANAGER — /manager/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin,manager')
        ->prefix('manager')
        ->name('manager.')
        ->group(function () {
            Route::get('/dashboard',        [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/dashboard/filter', [ReportController::class, 'dashboardFilter'])->name('dashboard.filter');

            // ── Kategori ─────────────────────────────────────────────────
            Route::resource('categories', CategoryController::class)->except(['show']);

            // ── Produk ────────────────────────────────────────────────────
            Route::get('products/trashed', [ProductController::class, 'trashed'])->name('products.trashed');
            Route::resource('products', ProductController::class)->except(['show']);
            Route::patch('products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');

            // Variants
            Route::post('products/{product}/variants',             [ProductController::class, 'storeVariant'])->name('products.variants.store');
            Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

            // Discounts
            Route::post('products/{product}/discounts',                    [ProductController::class, 'storeDiscount'])->name('products.discounts.store');
            Route::delete('products/{product}/discounts/{discount}',       [ProductController::class, 'destroyDiscount'])->name('products.discounts.destroy');
            Route::patch('products/{product}/discounts/{discount}/toggle', [ProductController::class, 'toggleDiscount'])->name('products.discounts.toggle');

            // ── Bundle Packages ───────────────────────────────────────────
            Route::get('bundles',               [ProductController::class, 'bundles'])->name('bundles.index');
            Route::get('bundles/create',        [ProductController::class, 'createBundle'])->name('bundles.create');
            Route::post('bundles',              [ProductController::class, 'storeBundle'])->name('bundles.store');
            Route::get('bundles/{bundle}/edit', [ProductController::class, 'editBundle'])->name('bundles.edit');
            Route::put('bundles/{bundle}',      [ProductController::class, 'updateBundle'])->name('bundles.update');
            Route::delete('bundles/{bundle}',   [ProductController::class, 'destroyBundle'])->name('bundles.destroy');

            // ── Meja ──────────────────────────────────────────────────────
            Route::post('tables/bulk', [\App\Http\Controllers\Manager\TableManagerController::class, 'storeBulk'])->name('tables.bulk');
            Route::resource('tables', \App\Http\Controllers\Manager\TableManagerController::class)->except(['show']);

            // ── Pengaturan Toko ───────────────────────────────────────────
            Route::get('settings',   [\App\Http\Controllers\Manager\StoreSettingsController::class, 'index'])->name('settings.index');
            Route::patch('settings', [\App\Http\Controllers\Manager\StoreSettingsController::class, 'update'])->name('settings.update');

            // ── Laporan ───────────────────────────────────────────────────
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/',         [ReportController::class, 'index'])->name('index');
                Route::get('/sales',    [ReportController::class, 'sales'])->name('sales');
                Route::get('/products', [ReportController::class, 'products'])->name('products');
                Route::get('/revenue',  [ReportController::class, 'revenue'])->name('revenue');
                Route::get('/cashiers', [ReportController::class, 'cashiers'])->name('cashiers');
                Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
                Route::get('/{type}/download',    [ReportController::class, 'download'])->name('download');
                Route::post('/{type}/send-email', [ReportController::class, 'sendReportEmail'])->name('send-email');
            });

            // Payment refund (manager)
            Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

            // Order management (manager override)
            Route::post('orders/{order}/cancel',         [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('orders/{order}/transfer-table', [TableController::class, 'transfer'])->name('orders.transfer-table');
        });

    /*──────────────────────────────────────────────────────
    |  CASHIER — /cashier/...
    ──────────────────────────────────────────────────────*/
    Route::middleware('role:admin,manager,cashier')
        ->prefix('cashier')
        ->name('cashier.')
        ->group(function () {

            // ── Snap finish redirect ───────────────────────────────────────
            Route::get('snap/finish', function (Request $request) {
                $midtransOrderId = $request->get('order_id', '');
                $orderNumber = preg_replace('/-\d+$/', '', $midtransOrderId);
                $order = \App\Models\Order::where('order_number', $orderNumber)->first();
                if ($order) {
                    return redirect()->route('cashier.orders.show', $order)
                        ->with('info', 'Pembayaran sedang diproses. Halaman akan diperbarui otomatis.');
                }
                return redirect()->route('cashier.orders.index');
            })->name('snap.finish');

            // ── Orders ────────────────────────────────────────────────────
            Route::resource('orders', OrderController::class)->except(['destroy']);
            Route::post('orders/{order}/cancel',   [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::post('orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
            Route::patch('orders/{order}/status',  [OrderController::class, 'updateStatus'])->name('orders.status');

            // ── Payments ──────────────────────────────────────────────────
            Route::get('orders/{order}/payment',           [PaymentController::class, 'create'])->name('payments.create');
            Route::post('orders/{order}/payment',          [PaymentController::class, 'store'])->name('payments.store');
            Route::post('orders/{order}/payment/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
            Route::get('payments/{payment}/poll',          [PaymentController::class, 'pollStatus'])->name('payments.poll');
            Route::delete('payments/{payment}/cancel-pending', [PaymentController::class, 'cancelPending'])->name('payments.cancel-pending');

            // ── Receipts ──────────────────────────────────────────────────
            Route::get('receipts/{payment}',        [ReceiptController::class, 'show'])->name('receipts.show');
            Route::get('receipts/{payment}/print',  [ReceiptController::class, 'print'])->name('receipts.print');
            Route::post('receipts/{payment}/email', [ReceiptController::class, 'sendEmail'])->name('receipts.send-email');

            // ── Tables & Reservations ─────────────────────────────────────
            Route::get('tables', [TableController::class, 'index'])->name('tables.index');

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