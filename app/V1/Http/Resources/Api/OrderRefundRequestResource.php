<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRefundRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order' => new OrderResource($this->whenLoaded('order')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->status,
            'reason' => $this->reason,
            'details' => $this->details
        ];
    }
}
