#!/bin/bash
# =============================================================================
# DePOS — Fix Migration & Database
# Jalankan dari root project: bash fix-migrate.sh
#
# Menyelesaikan:
#  1. Tabel 'users' tidak ada (migration belum jalan)
#  2. Default migration Laravel 12 bentrok dengan migration DePOS
#  3. LoginThrottle crash jika tabel belum ada
#  4. Cache lama yang bisa menyebabkan masalah
# =============================================================================
set -e

echo "╔══════════════════════════════════════════╗"
echo "║   DePOS — Fix Migration & Database       ║"
echo "╚══════════════════════════════════════════╝"

# ── STEP 1: Fix LoginThrottle ────────────────────────────────────────────────
echo ""
echo "📝 STEP 1: Fix LoginThrottle (aman jika tabel belum ada)..."

cat > app/Http/Middleware/LoginThrottle.php << 'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LoginThrottle
{
    public const MAX_ATTEMPTS    = 5;
    public const LOCKOUT_MINUTES = 15;

    public function handle(Request $request, Closure $next): Response
    {
        // Guard: skip jika tabel belum ada (sebelum migrate)
        if (! Schema::hasTable('users')) {
            return $next($request);
        }

        $email = $request->input('email');

        if ($email) {
            $user = DB::table('users')->where('email', $email)->first();

            if ($user && $user->locked_until && now()->lt($user->locked_until)) {
                $remaining = now()->diffInMinutes($user->locked_until) + 1;

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Akun dikunci. Coba lagi dalam {$remaining} menit.",
                    ], 429);
                }

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => "Akun dikunci. Coba lagi dalam {$remaining} menit."]);
            }
        }

        return $next($request);
    }

    public static function recordFailure(int $userId): void
    {
        if (! Schema::hasTable('users')) return;
        $user = DB::table('users')->where('id', $userId)->first();
        if (! $user) return;

        $attempts = $user->failed_login_attempts + 1;
        $update   = ['failed_login_attempts' => $attempts, 'updated_at' => now()];

        if ($attempts >= self::MAX_ATTEMPTS) {
            $update['locked_until'] = now()->addMinutes(self::LOCKOUT_MINUTES);
        }

        DB::table('users')->where('id', $userId)->update($update);
    }

    public static function clearFailures(int $userId): void
    {
        if (! Schema::hasTable('users')) return;

        DB::table('users')->where('id', $userId)->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
            'updated_at'            => now(),
        ]);
    }
}
PHP

echo "   ✅ LoginThrottle.php diperbaiki"

# ── STEP 2: Hapus default migration Laravel yang BENTROK ─────────────────────
echo ""
echo "📝 STEP 2: Hapus default migration Laravel yang bentrok dengan DePOS..."
echo ""
echo "   Laravel 12 secara default membuat migration berikut:"
echo "   - 0001_01_01_000000_create_users_table.php    → BENTROK dengan DePOS users migration"
echo "   - 0001_01_01_000001_create_cache_table.php    → DIPERTAHANKAN (cache tetap dibutuhkan)"
echo "   - 0001_01_01_000002_create_jobs_table.php     → DIPERTAHANKAN (queue jobs)"
echo ""

MIGRATION_DIR="database/migrations"

# Cari dan hapus default users migration Laravel (nama bisa berbeda tiap versi)
for f in \
    "$MIGRATION_DIR/0001_01_01_000000_create_users_table.php" \
    "$MIGRATION_DIR/2014_10_12_000000_create_users_table.php" \
    "$MIGRATION_DIR/2014_10_12_100000_create_password_reset_tokens_table.php" \
    "$MIGRATION_DIR/2019_08_19_000000_create_failed_jobs_table.php" \
    "$MIGRATION_DIR/2019_12_14_000001_create_personal_access_tokens_table.php"; do
    if [ -f "$f" ]; then
        echo "   🗑️  Menghapus: $(basename $f)"
        rm -f "$f"
    fi
done

echo "   ✅ Default migrations yang bentrok dihapus"

# ── STEP 3: Pastikan semua DePOS migration ada ───────────────────────────────
echo ""
echo "📝 STEP 3: Verifikasi DePOS migration files..."

REQUIRED=(
    "2026_02_12_000001_create_roles_table.php"
    "2026_02_12_000002_create_stores_table.php"
    "2026_02_12_000003_create_users_table.php"
    "2026_02_12_000004_create_categories_table.php"
    "2026_02_12_000005_create_products_table.php"
    "2026_02_12_000006_create_tables_table.php"
    "2026_02_12_000007_create_reservations_table.php"
    "2026_02_12_000008_create_orders_table.php"
    "2026_02_12_000009_create_order_items_table.php"
    "2026_02_12_000010_create_payments_table.php"
    "2026_02_12_000011_create_kitchen_orders_table.php"
    "2026_02_12_000012_create_activity_logs_table.php"
    "2026_02_12_000013_create_stock_logs_and_report_schedules_tables.php"
)

ALL_OK=true
for f in "${REQUIRED[@]}"; do
    if [ -f "$MIGRATION_DIR/$f" ]; then
        echo "   ✅ $f"
    else
        echo "   ❌ TIDAK ADA: $f  ← Salin dari folder outputs/migrations/"
        ALL_OK=false
    fi
done

if [ "$ALL_OK" = false ]; then
    echo ""
    echo "   ⚠️  Ada migration DePOS yang hilang!"
    echo "   Salin dari outputs/migrations/ ke database/migrations/"
    echo "   lalu jalankan script ini lagi."
    exit 1
fi

# ── STEP 4: Pastikan cache_table dan jobs_table ada ──────────────────────────
echo ""
echo "📝 STEP 4: Membuat migration cache & jobs jika belum ada..."

if ! ls "$MIGRATION_DIR"/*cache_table* 2>/dev/null | grep -q .; then
    echo "   Membuat cache table migration..."
    php artisan make:migration create_cache_table --create=cache 2>/dev/null || true
fi

if ! ls "$MIGRATION_DIR"/*jobs_table* 2>/dev/null | grep -q .; then
    echo "   Membuat jobs table migration..."
    php artisan make:migration create_jobs_table --create=jobs 2>/dev/null || true
fi

# ── STEP 5: Clear semua cache sebelum migrate ────────────────────────────────
echo ""
echo "📝 STEP 5: Clear semua cache..."
php artisan config:clear   2>/dev/null
php artisan route:clear    2>/dev/null
php artisan view:clear     2>/dev/null
php artisan cache:clear    2>/dev/null
php artisan event:clear    2>/dev/null
composer dump-autoload -q

echo "   ✅ Cache dibersihkan"

# ── STEP 6: Jalankan migration ───────────────────────────────────────────────
echo ""
echo "📝 STEP 6: Menjalankan migration..."
echo ""

php artisan migrate:fresh --force

echo ""
echo "   ✅ Semua tabel berhasil dibuat"

# ── STEP 7: Jalankan seeder ──────────────────────────────────────────────────
echo ""
echo "📝 STEP 7: Menjalankan seeder (mengisi data awal)..."
echo ""

php artisan db:seed --force

echo ""
echo "   ✅ Data awal berhasil diisi"

# ── STEP 8: Verifikasi tabel yang dibuat ────────────────────────────────────
echo ""
echo "📝 STEP 8: Verifikasi tabel..."
php artisan tinker --execute="
    \$tables = ['roles','stores','users','categories','products','tables',
                'reservations','orders','order_items','payments',
                'kitchen_orders','activity_logs','stock_logs','report_schedules'];
    foreach (\$tables as \$t) {
        \$count = DB::table(\$t)->count();
        echo \"   ✅ \$t → \$count rows\n\";
    }
" 2>/dev/null || echo "   (Verifikasi manual: php artisan tinker)"

# ── STEP 9: Verifikasi user login ────────────────────────────────────────────
echo ""
echo "📝 STEP 9: Verifikasi user bisa login..."
php artisan tinker --execute="
    \$user = DB::table('users')->where('email', 'admin@depos.id')->first();
    if (\$user) {
        echo '   ✅ admin@depos.id ditemukan, role_id=' . \$user->role_id . PHP_EOL;
    } else {
        echo '   ❌ User admin tidak ditemukan!' . PHP_EOL;
    }
" 2>/dev/null || true

echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║  ✅ SELESAI! Database siap digunakan.               ║"
echo "║                                                      ║"
echo "║  Login credentials:                                  ║"
echo "║  Admin:   admin@depos.id     / Admin@12345           ║"
echo "║  Manager: manager.pusat@depos.id / Manager@12345     ║"
echo "║  Kasir:   kasir1.pusat@depos.id  / Cashier@12345    ║"
echo "║  Dapur:   dapur1.pusat@depos.id  / Kitchen@12345    ║"
echo "║                                                      ║"
echo "║  Akses: http://172.16.10.124                        ║"
echo "╚══════════════════════════════════════════════════════╝"
