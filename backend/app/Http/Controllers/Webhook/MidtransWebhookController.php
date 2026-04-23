<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Gateway\MidtransGateway;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Controller untuk menerima webhook notifikasi dari Midtrans.
 *
 * SETUP YANG DIPERLUKAN:
 *
 * 1. Tambahkan route di routes/api.php:
 *    Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle']);
 *
 * 2. Kecualikan dari CSRF (bootstrap/app.php atau VerifyCsrfToken.php):
 *    ->withMiddleware(function (Middleware $middleware) {
 *        $middleware->validateCsrfTokens(except: ['api/webhook/*']);
 *    })
 *
 * 3. Di dashboard Midtrans → Settings → Configuration:
 *    - Payment Notification URL: https://yourdomain.com/api/webhook/midtrans
 *    - Finish/Unfinish/Error Redirect URL: https://yourdomain.com/cashier/... (opsional)
 *
 * 4. Untuk local development, gunakan ngrok:
 *    ngrok http 8000
 *    → set URL ngrok ke dashboard Midtrans
 *
 * PENTING: Midtrans akan retry webhook jika tidak menerima HTTP 200 dalam batas waktu.
 * Selalu return 200 kecuali signature tidak valid (401) atau error server (500).
 */
class MidtransWebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private MidtransGateway $gateway,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', [
            'order_id'          => $payload['order_id']          ?? null,
            'transaction_id'    => $payload['transaction_id']    ?? null,
            'transaction_status'=> $payload['transaction_status']?? null,
            'payment_type'      => $payload['payment_type']      ?? null,
        ]);

        // ── 1. Verifikasi signature — WAJIB, jangan pernah skip ──────────
        if (! $this->gateway->verifyWebhook($payload)) {
            Log::warning('Midtrans webhook: signature tidak valid', [
                'order_id' => $payload['order_id'] ?? null,
                'ip'       => $request->ip(),
            ]);
            return response('Unauthorized', 401);
        }

        // ── 2. Proses webhook ─────────────────────────────────────────────
        try {
            $this->paymentService->handleWebhook($payload);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook: gagal diproses', [
                'order_id' => $payload['order_id'] ?? null,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            // Return 500 agar Midtrans tahu harus retry
            return response('Internal Server Error', 500);
        }

        // ── 3. Return 200 agar Midtrans tidak retry ───────────────────────
        return response('OK', 200);
    }
}