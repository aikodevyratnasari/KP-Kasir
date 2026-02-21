<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after authentication.
 * Rejects users whose account is 'inactive' or whose account is temporarily
 * locked due to too many failed login attempts.
 */
class CheckAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Hard deactivation by admin
        if ($user->status === 'inactive') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        // Temporary lock (5 failed attempts → 15-minute lockout)
        if ($user->locked_until && now()->lt($user->locked_until)) {
            $remaining = now()->diffInMinutes($user->locked_until) + 1;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Akun dikunci sementara. Coba lagi dalam {$remaining} menit.",
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', "Akun dikunci sementara. Coba lagi dalam {$remaining} menit.");
        }

        return $next($request);
    }
}
