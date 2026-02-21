#!/bin/bash
# =============================================================================
# DePOS — Full Setup (Dijalankan dari HOST, bukan dalam container)
# Usage: cd ~/pos-system && bash backend/setup.sh
# =============================================================================

APP="pos_app"

echo "╔══════════════════════════════════════════╗"
echo "║        DePOS — Full Setup                ║"
echo "╚══════════════════════════════════════════╝"

# ── Cek container berjalan ───────────────────────────────────────────────────
echo ""
echo "🔍 Cek container..."
if ! docker compose ps | grep -q "pos_app.*running\|pos_app.*Up"; then
    echo "   ❌ Container pos_app tidak berjalan!"
    echo "   Jalankan: docker compose up -d"
    exit 1
fi
if ! docker compose ps | grep -q "pos_postgres.*running\|pos_postgres.*Up"; then
    echo "   ❌ Container pos_postgres tidak berjalan!"
    echo "   Jalankan: docker compose up -d"
    exit 1
fi
echo "   ✅ Container berjalan"

# ── Hapus default migration Laravel yang bentrok ─────────────────────────────
echo ""
echo "📝 Hapus default migration Laravel yang bentrok..."
docker compose exec -T app bash -c "
    for f in \
        database/migrations/0001_01_01_000000_create_users_table.php \
        database/migrations/2014_10_12_000000_create_users_table.php \
        database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php \
        database/migrations/2019_08_19_000000_create_failed_jobs_table.php \
        database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php; do
        [ -f \"\$f\" ] && rm -f \"\$f\" && echo \"   🗑️  Hapus: \$(basename \$f)\"
    done
    echo '   ✅ Selesai'
"

# ── Cek DePOS migrations ada ─────────────────────────────────────────────────
echo ""
echo "📝 Cek DePOS migration files..."
MISSING=0
for f in \
    "2026_02_12_000001_create_roles_table.php" \
    "2026_02_12_000002_create_stores_table.php" \
    "2026_02_12_000003_create_users_table.php" \
    "2026_02_12_000004_create_categories_table.php" \
    "2026_02_12_000005_create_products_table.php" \
    "2026_02_12_000006_create_tables_table.php" \
    "2026_02_12_000007_create_reservations_table.php" \
    "2026_02_12_000008_create_orders_table.php" \
    "2026_02_12_000009_create_order_items_table.php" \
    "2026_02_12_000010_create_payments_table.php" \
    "2026_02_12_000011_create_kitchen_orders_table.php" \
    "2026_02_12_000012_create_activity_logs_table.php" \
    "2026_02_12_000013_create_stock_logs_and_report_schedules_tables.php"; do
    EXISTS=$(docker compose exec -T app bash -c "[ -f database/migrations/$f ] && echo yes || echo no")
    if [ "$EXISTS" = "yes" ] || echo "$EXISTS" | grep -q "yes"; then
        echo "   ✅ $f"
    else
        echo "   ❌ TIDAK ADA: $f"
        MISSING=1
    fi
done

if [ "$MISSING" = "1" ]; then
    echo ""
    echo "   ❌ Ada migration yang hilang!"
    echo "   Pastikan file migration DePOS ada di backend/database/migrations/"
    exit 1
fi

# ── composer dump-autoload ────────────────────────────────────────────────────
echo ""
echo "📝 composer dump-autoload..."
docker compose exec -T app composer dump-autoload -q
echo "   ✅ Autoload OK"

# ── Clear semua cache ─────────────────────────────────────────────────────────
echo ""
echo "📝 Clear cache Laravel..."
docker compose exec -T app php artisan config:clear 2>/dev/null || true
docker compose exec -T app php artisan route:clear  2>/dev/null || true
docker compose exec -T app php artisan view:clear   2>/dev/null || true
docker compose exec -T app php artisan cache:clear  2>/dev/null || true
echo "   ✅ Cache cleared"

# ── Tunggu PostgreSQL siap ────────────────────────────────────────────────────
echo ""
echo "📝 Menunggu PostgreSQL siap..."
for i in $(seq 1 15); do
    if docker compose exec -T app php artisan migrate:status 2>/dev/null | grep -q "Ran\|Pending\|No migrations" 2>/dev/null; then
        echo "   ✅ PostgreSQL siap (${i}s)"
        break
    fi
    if [ $i -eq 15 ]; then
        echo "   ⚠️  PostgreSQL lambat, coba lanjutkan..."
    fi
    sleep 1
    echo -n "   ."
done

# ── migrate:fresh ─────────────────────────────────────────────────────────────
echo ""
echo "📝 Jalankan migrate:fresh..."
echo ""
docker compose exec -T app php artisan migrate:fresh --force
echo ""
echo "   ✅ Semua tabel berhasil dibuat"

# ── Seeder ────────────────────────────────────────────────────────────────────
echo ""
echo "📝 Jalankan seeder..."
echo ""
docker compose exec -T app php artisan db:seed --force
echo ""
echo "   ✅ Data awal berhasil diisi"

# ── Verifikasi ────────────────────────────────────────────────────────────────
echo ""
echo "📝 Verifikasi..."
docker compose exec -T app php artisan tinker --execute="
    \$checks = ['roles','stores','users','categories','products','tables','orders','payments','kitchen_orders','activity_logs'];
    \$ok = true;
    foreach(\$checks as \$t) {
        try {
            \$n = DB::table(\$t)->count();
            echo '   ✅ ' . str_pad(\$t, 20) . \$n . ' rows' . PHP_EOL;
        } catch(\Exception \$e) {
            echo '   ❌ ' . \$t . ' — ERROR' . PHP_EOL;
            \$ok = false;
        }
    }
    \$user = DB::table('users')->where('email','admin@depos.id')->first();
    echo PHP_EOL;
    echo \$user ? '   ✅ admin@depos.id siap' : '   ❌ User admin tidak ditemukan';
    echo PHP_EOL;
" 2>/dev/null || echo "   (tinker tidak tersedia, cek manual)"

# ── Done ─────────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║  ✅ Setup selesai! Siap digunakan.                  ║"
echo "║                                                      ║"
echo "║  Login:                                              ║"
echo "║  Admin:   admin@depos.id       / Admin@12345         ║"
echo "║  Manager: manager.pusat@depos.id / Manager@12345     ║"
echo "║  Kasir:   kasir1.pusat@depos.id  / Cashier@12345    ║"
echo "║  Dapur:   dapur1.pusat@depos.id  / Kitchen@12345    ║"
echo "║                                                      ║"
echo "║  URL: http://172.16.10.124                          ║"
echo "╚══════════════════════════════════════════════════════╝"