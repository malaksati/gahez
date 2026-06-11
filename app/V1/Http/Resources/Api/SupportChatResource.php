<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'assigned_admin_id' => $this->assigned_admin_id,
            'assigned_admin' => new UserResource($this->whenLoaded('assignedAdmin')),
            'status' => $this->status,
            'subject' => $this->subject,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'latest_message' => new SupportMessageResource($this->whenLoaded('latestMessage')),
            'unread_messages_count' => $this->when(
                isset($this->unread_messages_count),
                (int) $this->unread_messages_count,
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
