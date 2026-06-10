<?php

namespace App\V1\Http\Resources\Api;

use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductUnitResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    public function toArray(Request $request): array
    {
        $unit = $this->whenLoaded('unit');

        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'variant_label' => $this->when(
                $this->product_variant_id,
                fn () => $this->variantLabel($request->getLocale()),
            ),
            'unit_id' => $this->unit_id,
            'name' => $unit
                ? $this->localizedValue($unit->getTranslations('name'), null, $request)
                : null,
            'factor' => max(1, (int) $this->factor),
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'final_price' => (float) $this->final_price,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'stock' => $this->stock,
            'is_in_stock' => $this->is_in_stock,
            'is_default' => $this->is_default,
            'label' => $this->displayUnitName($request->getLocale()),
        ];
    }
}
