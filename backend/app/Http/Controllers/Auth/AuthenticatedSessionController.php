<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\LoginThrottle;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Pre-check akun dikunci
        $userRecord = User::where('email', $request->email)->first();

        if ($userRecord && $userRecord->isLocked()) {
            $remaining = now()->diffInMinutes($userRecord->locked_until) + 1;
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => "Akun dikunci. Coba lagi dalam {$remaining} menit."]);
        }

        // Autentikasi
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            if ($userRecord) {
                LoginThrottle::recordFailure($userRecord->id);
                $fresh     = $userRecord->fresh();
                $remaining = LoginThrottle::MAX_ATTEMPTS - $fresh->failed_login_attempts;

                if ($remaining <= 0) {
                    return back()->withInput($request->only('email'))
                        ->withErrors(['email' => 'Akun dikunci 15 menit karena terlalu banyak percobaan gagal.']);
                }

                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => "Email atau password salah. Sisa percobaan: {$remaining}."]);
            }

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $user = Auth::user();

        // Cek status inactive
        if ($user->status === 'inactive') {
            Auth::logout();
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi administrator.']);
        }

        LoginThrottle::clearFailures($user->id);
        ActivityLogService::log('login', $user, description: "Login: {$user->email}");

        $request->session()->regenerate();

        // Redirect ke dashboard sesuai role
        return redirect()->intended($user->dashboardRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        ActivityLogService::log('logout', $user, description: "Logout: {$user?->email}");

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil logout.');
    }
}
