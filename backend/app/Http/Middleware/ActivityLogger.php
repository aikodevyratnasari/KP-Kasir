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
