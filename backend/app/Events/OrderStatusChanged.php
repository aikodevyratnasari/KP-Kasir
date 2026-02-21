<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order  $order,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("store.{$this->order->store_id}.kitchen"),
            new Channel("store.{$this->order->store_id}.cashier"),
        ];
    }

    public function broadcastAs(): string { return 'order.status_changed'; }

    public function broadcastWith(): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status'   => $this->oldStatus,
            'new_status'   => $this->newStatus,
            'table'        => $this->order->table?->number,
        ];
    }
}