<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Address
 */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'name' => $this->name,
            'phone' => $this->phone,
            'city' => $this->city,
            'state' => $this->state,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'user' => new UserResource($this->user),
        ];
    }
}
