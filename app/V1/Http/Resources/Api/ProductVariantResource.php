<?php

namespace App\V1\Http\Resources\Api;

use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name', null, $request),
            'slug' => $this->slug,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'price' => $this->price,
            'is_active' => $this->is_active ?? true,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'values' => ProductVariantValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
