<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendFileBase64(string $phone, string $message, string $base64, string $filename): bool
    {
        $normalized = str_starts_with($phone, '0')
            ? '62' . substr($phone, 1)
            : ltrim($phone, '+');

        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target'   => $normalized,
            'message'  => $message,
            'file'     => $base64,
            'filename' => $filename,
        ]);

        return $response->successful() && ($response->json('status') === true);
    }
}