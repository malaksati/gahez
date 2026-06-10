<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderDeliveredAdminNotification extends Notification
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
            'title' => __('messages.Order delivered'),
            'message' => __('messages.Order #:id has been delivered successfully.', ['id' => $this->order->id]),
            'url' => route('v1.admin.orders.show', $this->order),
            'order_id' => $this->order->id,
            'status' => $this->order->status,
        ];
    }
}
