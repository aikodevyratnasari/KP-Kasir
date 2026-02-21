<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects the authenticated user's store_id into the request so all
 * subsequent queries can be scoped to their store automatically.
 *
 * Admins may optionally pass ?store_id=X to switch context.
 * All other roles are hard-locked to their own store.
 */
class StoreScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $roleSlug = $user->role->slug ?? null;

        // Admin can optionally target a different store via query param
        if ($roleSlug === 'admin' && $request->filled('store_id')) {
            $storeId = (int) $request->query('store_id');
        } else {
            $storeId = $user->store_id;
        }

        // Make the resolved store_id available throughout the request lifecycle
        App::instance('current_store_id', $storeId);
        $request->merge(['_store_id' => $storeId]);

        return $next($request);
    }
}
