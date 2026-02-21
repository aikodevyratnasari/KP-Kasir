<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:
 *   Route::middleware('role:admin')->group(...)
 *   Route::middleware('role:admin,manager')->group(...)
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $this->unauthorized($request, 'Unauthenticated.');
        }

        $userSlug = $user->role->slug ?? null;

        if (! $userSlug || ! in_array($userSlug, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Insufficient role permissions.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }

    private function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 401);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
