<?php

namespace App\V1\Http\Resources\Api;

use App\Models\Offer;
use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Offer
 */
class OfferResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name', null, $request),
            'type' => $this->type,
            'value' => (float) $this->value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'show_countdown' => $this->shouldShowCountdown(),
            'countdown_ends_at' => $this->shouldShowCountdown() ? $this->end_date : null,
            'is_valid' => $this->isValid(),
            'offerable_type' => $this->offerable_type,
            'offerable_type_short' => class_basename((string) $this->offerable_type),
            'offerable_id' => $this->offerable_id,
            'offerable' => new OfferableResource($this->whenLoaded('offerable')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
