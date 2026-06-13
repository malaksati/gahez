<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderForAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('messages.New order'),
            'message' => __('messages.A new order #:id has been placed.', ['id' => $this->order->id]),
            'url' => route('v1.admin.orders.show', $this->order),
            'order_id' => $this->order->id,
        ];
    }
}
