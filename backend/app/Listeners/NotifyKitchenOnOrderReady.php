<?php
namespace App\Listeners;
use App\Events\OrderStatusChanged;
use App\Models\User;
use App\Notifications\OrderReadyNotification;
use Illuminate\Support\Facades\Notification;

class NotifyKitchenOnOrderReady
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->newStatus !== 'ready') return;
        $cashiers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['cashier', 'manager']))
            ->where('store_id', $event->order->store_id)->where('status', 'active')->get();
        Notification::send($cashiers, new OrderReadyNotification($event->order));
    }
}
