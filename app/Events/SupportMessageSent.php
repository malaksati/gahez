<?php

namespace App\Events;

use App\Models\SupportMessage;
use App\V1\Http\Resources\Api\SupportMessageResource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageSent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SupportMessage $message)
    {
        $this->message->loadMissing('sender');
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.'.$this->message->support_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'support.message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'support_id' => $this->message->support_id,
            'message' => (new SupportMessageResource($this->message))->resolve(),
        ];
    }
}
