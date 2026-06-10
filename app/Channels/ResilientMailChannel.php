<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResilientMailChannel extends MailChannel
{
    public function send($notifiable, Notification $notification): void
    {
        try {
            parent::send($notifiable, $notification);
        } catch (Throwable $exception) {
            Log::warning('Notification mail delivery failed', [
                'notification' => $notification::class,
                'notifiable_id' => $notifiable->id ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
