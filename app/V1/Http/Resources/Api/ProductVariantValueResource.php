<?php

namespace App\V1\Http\Resources\Api;

use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantValueResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variant_option_id' => $this->variant_option_id,
            'variant_option' => new VariantOptionResource($this->whenLoaded('variantOption')),
            'product_variant_id' => $this->product_variant_id,
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'value' => $this->localized('value', null, $request),
            'thumbnail' => $this->thumbnail_url,
        ];
    }
}
