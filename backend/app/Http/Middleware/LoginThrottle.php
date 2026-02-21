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
