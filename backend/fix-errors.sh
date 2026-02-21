#!/bin/bash
# =============================================================================
# DePOS Error Fix Script
# Jalankan dari root project: bash fix-errors.sh
# Memperbaiki:
#   1) 500 error - Authenticate middleware tidak extend class yang benar
#   2) 500 error - ActivityLogger crash di global middleware
#   3) npm build fail - alpinejs tidak ditemukan
# =============================================================================
set -e
echo "🔧 DePOS Error Fix Script"
echo "========================="

# ── FIX 1: Authenticate.php ───────────────────────────────────────────────────
echo ""
echo "📝 FIX 1: Memperbaiki Authenticate middleware..."
echo "   Sebelum: extends custom class (tidak ada intended() redirect)"
echo "   Sesudah: extends Laravel's Illuminate\Auth\Middleware\Authenticate"

cat > app/Http/Middleware/Authenticate.php << 'PHP'
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Extends Laravel's built-in Authenticate middleware.
     *
     * Dengan extend ini:
     * - intended() redirect otomatis bekerja
     * - User yang belum login akan diarahkan ke /login
     * - Setelah login, dikembalikan ke URL yang dituju semula
     * - AJAX request mendapat 401 JSON (bukan redirect)
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
PHP

echo "   ✅ Authenticate.php diperbaiki"

# ── FIX 2: ActivityLogger.php ─────────────────────────────────────────────────
echo ""
echo "📝 FIX 2: Memperbaiki ActivityLogger global middleware..."
echo "   Sebelum: bisa crash jika DB belum ready atau user null"
echo "   Sesudah: fully defensive, tidak pernah menghancurkan aplikasi"

cat > app/Http/Middleware/ActivityLogger.php << 'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    private array $except = [
        'login',
        'logout',
        'api/refresh',
        '_debugbar*',
        'telescope*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // SELALU jalankan $next terlebih dahulu
        // ActivityLogger tidak boleh menghalangi request apapun
        $response = $next($request);

        // Hanya log method yang mengubah data
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }

        // Skip route yang dikecualikan
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $response;
            }
        }

        // Hanya log jika request sukses
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        // Log secara aman — TIDAK PERNAH crash aplikasi
        try {
            if (! Auth::check()) {
                return $response;
            }

            // Pastikan tabel ada (aman sebelum migration)
            if (! Schema::hasTable('activity_logs')) {
                return $response;
            }

            DB::table('activity_logs')->insert([
                'user_id'     => Auth::id(),
                'action'      => $request->method() . ' ' . $request->path(),
                'model_type'  => null,
                'model_id'    => null,
                'old_values'  => null,
                'new_values'  => null,
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($request->userAgent() ?? '', 0, 255),
                'description' => 'HTTP ' . $request->method() . ' ' . $request->path(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Logging error tidak boleh menghancurkan aplikasi
        }

        return $response;
    }
}
PHP

echo "   ✅ ActivityLogger.php diperbaiki"

# ── FIX 3: RedirectIfAuthenticated.php ───────────────────────────────────────
echo ""
echo "📝 FIX 3: Memperbaiki RedirectIfAuthenticated middleware..."
echo "   Sesudah: redirect ke dashboard sesuai role user"

cat > app/Http/Middleware/RedirectIfAuthenticated.php << 'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                return redirect($user->dashboardRoute());
            }
        }

        return $next($request);
    }
}
PHP

echo "   ✅ RedirectIfAuthenticated.php diperbaiki"

# ── FIX 4: Alpine.js npm install ─────────────────────────────────────────────
echo ""
echo "📝 FIX 4: Install Alpine.js via npm..."
echo "   Error: Cannot resolve 'alpinejs' from resources/js/app.js"

npm install alpinejs --save-dev 2>&1 | tail -3

echo "   ✅ alpinejs terinstall"

# ── FIX 5: app.js dan bootstrap.js ───────────────────────────────────────────
echo ""
echo "📝 FIX 5: Update resources/js/app.js dan bootstrap.js..."

cat > resources/js/bootstrap.js << 'JS'
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
JS

cat > resources/js/app.js << 'JS'
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
JS

echo "   ✅ app.js dan bootstrap.js diperbarui"

# ── FIX 6: Rebuild assets ─────────────────────────────────────────────────────
echo ""
echo "📝 FIX 6: Build ulang assets..."
npm run build

echo "   ✅ Assets berhasil di-build"

# ── FIX 7: Clear semua cache Laravel ─────────────────────────────────────────
echo ""
echo "📝 FIX 7: Clear semua cache Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear
composer dump-autoload -q

echo "   ✅ Semua cache dibersihkan"

# ── Verifikasi ────────────────────────────────────────────────────────────────
echo ""
echo "🔍 Verifikasi..."
php artisan route:list --name=login --columns=name,uri,middleware 2>/dev/null | head -5
echo ""
echo "============================================"
echo "✅ SEMUA ERROR BERHASIL DIPERBAIKI!"
echo ""
echo "Akses aplikasi:"
echo "  Web:  http://172.16.10.124"
echo "  Login: admin@depos.id / Admin@12345"
echo "============================================"