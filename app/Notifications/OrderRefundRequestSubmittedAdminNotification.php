<?php

namespace App\Notifications;

use App\Models\OrderRefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderRefundRequestSubmittedAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public OrderRefundRequest $refundRequest) {}

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
        $this->refundRequest->loadMissing('order', 'user');

        return [
            'title' => __('messages.New refund request'),
            'message' => __('messages.A customer requested a refund for order #:id.', [
                'id' => $this->refundRequest->order_id,
            ]),
            'url' => route('v1.admin.order-refund-requests.show', $this->refundRequest),
            'order_refund_request_id' => $this->refundRequest->id,
            'order_id' => $this->refundRequest->order_id,
        ];
    }
}
