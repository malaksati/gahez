<?php

namespace App\V1\Http\Resources\Api;

use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name', null, $request),
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ];
    }
}
