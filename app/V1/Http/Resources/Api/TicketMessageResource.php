<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'message' => $this->message,
            'attachments' => $this->when($this->attachments, function (){
                return collect($this->attachments)->map(function ($attachment) {
                    return asset('storage/'.$attachment);
                })->toArray();
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
