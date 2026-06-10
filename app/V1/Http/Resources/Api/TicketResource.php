<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'attachments' => $this->when($this->attachments, function () {
                return collect($this->attachments)->map(function ($attachment) {
                    return asset('storage/'.$attachment);
                })->toArray();
            }),
            'messages' => TicketMessageResource::collection($this->whenLoaded('messages')),
            'messages_count' => $this->when($this->relationLoaded('messages'), function () {
                return $this->messages->count();
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
