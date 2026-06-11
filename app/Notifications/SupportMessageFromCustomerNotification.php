<?php

namespace App\Notifications;

use App\Models\Support;
use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportMessageFromCustomerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Support $support,
        public SupportMessage $message,
    ) {}

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
            'type' => 'support_message_from_customer',
            'title' => __('messages.New support chat message'),
            'message' => __('messages.New message on support chat #:id', ['id' => $this->support->id]),
            'url' => route('v1.admin.support-chats.show', $this->support),
            'support_id' => $this->support->id,
            'support_message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type,
        ];
    }
}
