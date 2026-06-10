<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

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
            'title' => __('messages.New ticket'),
            'message' => __('messages.New support ticket: :subject', ['subject' => $this->ticket->subject]),
            'url' => route('v1.admin.tickets.show', $this->ticket),
            'ticket_id' => $this->ticket->id,
        ];
    }
}
