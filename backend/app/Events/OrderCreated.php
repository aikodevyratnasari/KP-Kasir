<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("store.{$this->order->store_id}.kitchen"),
            new Channel("store.{$this->order->store_id}.cashier"),
        ];
    }

    public function broadcastAs(): string { return 'order.created'; }

    public function broadcastWith(): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'table'        => $this->order->table?->number,
            'order_type'   => $this->order->order_type,
            'total'        => $this->order->total_amount,
            'items_count'  => $this->order->items->count(),
        ];
    }
}