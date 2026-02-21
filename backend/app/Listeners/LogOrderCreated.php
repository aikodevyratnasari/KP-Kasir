<?php
namespace App\Listeners;
use App\Events\OrderCreated;
use App\Services\ActivityLogService;

class LogOrderCreated
{
    public function handle(OrderCreated $event): void
    {
        ActivityLogService::logCreated($event->order, ['order_number' => $event->order->order_number]);
    }
}
