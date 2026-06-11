<?php

namespace App\Notifications;

use App\Models\Support;
use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SupportMessageFromAdminNotification extends Notification
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
            'type' => 'support_message',
            'title' => __('messages.New support chat message'),
            'message' => __('messages.Support agent replied to your chat'),
            'support_id' => $this->support->id,
            'support_message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type,
        ];
    }
}
