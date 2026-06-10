<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthdate' => $this->birthdate?->toDateString(),
            'image' => $this->image,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'roles' => $this->when(
                $this->relationLoaded('roles'),
                fn() => $this->roles->pluck('name')
            ),
            'permissions' => $this->when(
                $this->relationLoaded('permissions'),
                fn() => $this->permissions->pluck('name')
            ),
        ];
    }
}
