<?php
namespace App\Listeners;
use App\Events\LowStockAlert;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class NotifyManagersLowStock
{
    public function handle(LowStockAlert $event): void
    {
        $managers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'manager']))
            ->where('store_id', $event->product->store_id)->where('status', 'active')->get();
        Notification::send($managers, new LowStockNotification($event->product));
    }
}
