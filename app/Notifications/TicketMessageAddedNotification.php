<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketMessageAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketMessage $message,
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
            'title' => __('messages.New ticket message'),
            'message' => __('messages.New message on ticket #:id', ['id' => $this->ticket->id]),
            'url' => route('v1.admin.tickets.show', $this->ticket),
            'ticket_id' => $this->ticket->id,
            'ticket_message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type,
        ];
    }
}
