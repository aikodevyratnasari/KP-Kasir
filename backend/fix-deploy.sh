#!/bin/bash
# ================================================================
# DePOS — Deploy Alur Baru (Bayar → Dapur → Selesai → Meja Kosong)
# cd ~/pos-system && bash backend/deploy-flow.sh
# ================================================================
set -e
APP=pos_app

echo "🚀 Deploy alur order baru..."

# 1. Migration
echo "🗄️  Migration..."
docker compose cp \
    backend/database/migrations/2026_03_16_100000_add_flow_columns.php \
    $APP:/var/www/database/migrations/2026_03_16_100000_add_flow_columns.php
docker compose exec -T app php artisan migrate --force

# 2. Services
echo "⚙️  Services..."
docker compose cp backend/app/Services/PaymentService.php \
    $APP:/var/www/app/Services/PaymentService.php
docker compose cp backend/app/Services/OrderService.php \
    $APP:/var/www/app/Services/OrderService.php

# 3. Controllers
echo "🎮 Controllers..."
docker compose cp backend/app/Http/Controllers/Cashier/OrderController.php \
    $APP:/var/www/app/Http/Controllers/Cashier/OrderController.php
docker compose cp backend/app/Http/Controllers/Kitchen/KitchenDisplayController.php \
    $APP:/var/www/app/Http/Controllers/Kitchen/KitchenDisplayController.php

# 4. Views
echo "🖼️  Views..."
docker compose cp backend/resources/views/cashier/orders/show.blade.php \
    $APP:/var/www/resources/views/cashier/orders/show.blade.php
docker compose cp backend/resources/views/kitchen/display.blade.php \
    $APP:/var/www/resources/views/kitchen/display.blade.php

# 5. Fix KitchenOrder status untuk order lama
echo "🔧 Fix data lama..."
docker compose exec -T app php artisan tinker --execute="
    // Order yang sudah paid tapi kitchen_order masih waiting_payment → set queued
    \App\Models\Order::whereNotNull('sent_to_kitchen_at')
        ->whereIn('status', ['pending','cooking'])
        ->with('kitchenOrder')
        ->get()
        ->each(function(\$o) {
            if (\$o->kitchenOrder && \$o->kitchenOrder->status === 'waiting_payment') {
                \$o->kitchenOrder->update(['status' => 'queued', 'queued_at' => now()]);
            }
        });
    echo 'Data lama diperbaiki.' . PHP_EOL;
"

# 6. Clear cache
echo "🧹 Clear cache..."
docker compose exec -T app php artisan view:clear
docker compose exec -T app php artisan route:clear
docker compose exec -T app php artisan cache:clear

echo ""
echo "✅ Selesai!"
echo ""
echo "⚠️  JANGAN LUPA tambahkan ke routes/web.php (cashier group):"
echo "    Route::post('orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');"