<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->statusLabel();

        $message = (new MailMessage)
            ->subject(__('messages.Order status updated'))
            ->greeting(__('messages.Hello :name!', ['name' => $notifiable->name ?? __('messages.Customer')]))
            ->line(__('messages.Your order #:id status is now :status.', [
                'id' => $this->order->id,
                'status' => $statusLabel,
            ]));

        if ($this->order->status === 'cancelled' && $this->order->cancellation_reason) {
            $message->line(__('messages.Cancellation reason: :reason', [
                'reason' => $this->order->cancellation_reason,
            ]));
        }

        return $message->line(__('messages.Thank you for being with us!'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->statusLabel();

        return [
            'type' => 'order_status_updated',
            'title' => __('messages.Order status updated'),
            'message' => __('messages.Your order #:id status is now :status.', [
                'id' => $this->order->id,
                'status' => $statusLabel,
            ]),
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'cancellation_reason' => $this->order->cancellation_reason,
        ];
    }

    protected function statusLabel(): string
    {
        $key = 'messages.'.$this->order->status;
        $translated = __($key);

        return $translated !== $key ? $translated : ucfirst(str_replace('_', ' ', $this->order->status));
    }
}
