<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Middleware\LoginThrottle;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(Auth::user()->dashboardRoute());
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            $request->session()->regenerate();
            LoginThrottle::clearFailures($user->id);
            ActivityLogService::log('login', $user, description: "User {$user->email} logged in.");
            return redirect($user->dashboardRoute());
        }

        // Record failed attempt
        $userRow = \App\Models\User::where('email', $request->email)->first();
        if ($userRow) {
            LoginThrottle::recordFailure($userRow->id);
            $remaining = LoginThrottle::MAX_ATTEMPTS - $userRow->fresh()->failed_login_attempts;
            if ($remaining <= 0) {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Akun dikunci selama 15 menit karena terlalu banyak percobaan login.']);
            }
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        ActivityLogService::log('logout', $user, description: "User {$user?->email} logged out.");
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}