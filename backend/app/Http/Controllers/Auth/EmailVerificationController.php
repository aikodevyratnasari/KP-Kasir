<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($request->user()->dashboardRoute())
                ->with('success', 'Email Anda sudah diverifikasi sebelumnya.');
        }

        $request->fulfill();

        return redirect($request->user()->dashboardRoute())
            ->with('success', '✅ Email berhasil diverifikasi! Selamat datang di DePOS.');
    }

    /**
     * Kirim ulang email verifikasi.
     * Route: POST /email/verification-notification  → verification.send
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($request->user()->dashboardRoute());
        }

        // Throttle: maksimal 1 kali per menit
        $request->user()->notify(new VerifyEmailNotification());

        return back()->with('resent', true);
    }
}