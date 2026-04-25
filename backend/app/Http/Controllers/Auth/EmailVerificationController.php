<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Tampilkan halaman "email belum diverifikasi".
     * Route: GET /email/verify  → verification.notice
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($request->user()->dashboardRoute());
        }

        return view('auth.verify-email', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Proses link verifikasi yang diklik dari email.
     * Route: GET /email/verify/{id}/{hash}  → verification.verify
     *
     * Route ini TIDAK menggunakan middleware 'auth' — user tidak perlu login dulu.
     * Middleware yang dipakai hanya 'signed' untuk validasi URL.
     * Setelah verifikasi berhasil, user diarahkan ke halaman LOGIN.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        // Temukan user berdasarkan ID — tanpa perlu session login
        $user = User::findOrFail($id);

        // Validasi hash — pastikan link email sesuai dengan user ini
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Link verifikasi tidak valid.');
        }

        // Sudah diverifikasi sebelumnya
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('success', 'Email Anda sudah diverifikasi. Silakan login.');
        }

        // Tandai sebagai terverifikasi
        $user->markEmailAsVerified();

        return redirect()->route('login')
            ->with('success', '✅ Email berhasil diverifikasi! Silakan login untuk masuk ke DePOS.');
    }

    /**
     * Kirim ulang email verifikasi (user sudah login, belum verifikasi).
     * Route: POST /email/verification-notification  → verification.send
     * Throttle ditangani di level route: middleware('throttle:6,1')
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($request->user()->dashboardRoute());
        }

        $request->user()->notify(new VerifyEmailNotification());

        return back()->with('resent', true);
    }
}