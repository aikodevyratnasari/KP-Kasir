<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Waktu kadaluarsa link verifikasi (menit).
     * Default Laravel = 60 menit, kita set 72 jam agar user punya waktu cukup.
     */
    protected static $expireMinutes = 72 * 60;

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Akun DePOS Anda')
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Akun DePOS Anda telah dibuat oleh Administrator.')
            ->line('Silakan klik tombol di bawah untuk memverifikasi alamat email dan mengaktifkan akun Anda.')
            ->action('Verifikasi Email Saya', $verificationUrl)
            ->line('Link verifikasi ini akan kadaluarsa dalam **72 jam**.')
            ->line('Jika Anda tidak merasa mendaftar atau mendapatkan akun DePOS, abaikan email ini.')
            ->salutation('Salam, Tim DePOS');
    }

    /**
     * Generate URL verifikasi yang sudah di-sign.
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(static::$expireMinutes),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}