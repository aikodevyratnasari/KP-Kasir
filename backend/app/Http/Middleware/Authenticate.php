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
