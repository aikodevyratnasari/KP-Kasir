<?php
namespace App\Listeners;
use App\Events\PaymentProcessed;
use App\Services\ActivityLogService;

class LogPaymentProcessed
{
    public function handle(PaymentProcessed $event): void
    {
        ActivityLogService::log('payment_processed', $event->payment, null,
            ['amount' => $event->payment->amount, 'method' => $event->payment->payment_method],
            "Payment #{$event->payment->id} for order #{$event->payment->order->order_number}");
    }
}
