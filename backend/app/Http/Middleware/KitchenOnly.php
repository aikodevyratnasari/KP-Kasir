<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to Kitchen Staff (and admins/managers who may
 * need to monitor the kitchen display).
 *
 * Usage:
 *   Route::middleware('kitchen.access')->group(...)
 */
class KitchenOnly
{
    private array $allowed = ['admin', 'manager', 'kitchen_staff'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $roleSlug = $user->role->slug ?? null;

        if (! in_array($roleSlug, $this->allowed)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Akses hanya untuk staf dapur.'], 403)
                : abort(403, 'Akses hanya untuk staf dapur.');
        }

        return $next($request);
    }
}
