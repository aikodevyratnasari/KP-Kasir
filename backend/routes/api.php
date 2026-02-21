<?php

use App\Http\Controllers\Cashier\OrderController;
use App\Http\Controllers\Kitchen\KitchenDisplayController;
use Illuminate\Support\Facades\Route;

/*
|───────────────────────────────────────────────────────────────────────────────
|  API ROUTES  (prefix: /api, middleware: force.json)
|
|  These are used by the frontend JS for AJAX calls and real-time polling.
|  All routes require session-based auth (same session as web routes).
|───────────────────────────────────────────────────────────────────────────────
*/

Route::middleware(['auth', 'account.status', 'store.scope', 'force.json'])->group(function () {

    // Kitchen polling endpoint (no page reload needed)
    Route::middleware('kitchen.access')->group(function () {
        Route::get('/kitchen/orders',                       [KitchenDisplayController::class, 'poll']);
        Route::post('/kitchen/orders/{kitchenOrder}/start', [KitchenDisplayController::class, 'start']);
        Route::post('/kitchen/orders/{kitchenOrder}/ready', [KitchenDisplayController::class, 'ready']);
    });

    // Order status update (AJAX from cashier terminal)
    Route::middleware('role:admin,manager,cashier,kitchen_staff')
        ->patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);

    // Table status (for cashier dashboard refresh)
    Route::middleware('role:admin,manager,cashier')
        ->get('/tables', fn(\Illuminate\Http\Request $request) =>
            response()->json(
                \App\Models\Table::forStore($request->get('_store_id'))
                    ->with('activeOrder')
                    ->orderBy('section')
                    ->orderBy('number')
                    ->get()
            )
        );
});