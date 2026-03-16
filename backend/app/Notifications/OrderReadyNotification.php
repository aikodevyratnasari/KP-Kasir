<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class OrderReadyNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'table'        => $this->order->table?->number,
            'message'      => "Pesanan #{$this->order->order_number} siap disajikan!",
        ];
    }
}