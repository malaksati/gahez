<?php

namespace App\V1\Http\Resources\Api;

use App\Models\Category;
use App\Models\Product;
use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes the polymorphic target of an offer (product, category, …).
 *
 * @mixin Model
 */
class OfferableResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Product) {
            return [
                'kind' => 'product',
                'id' => $this->id,
                'name' => $this->localized('name', null, $request),
                'slug' => $this->slug,
                'thumbnail' => $this->main_image ?? $this->thumbnail,
                'price' => $this->price,
            ];
        }

        if ($this->resource instanceof Category) {
            return [
                'kind' => 'category',
                'id' => $this->id,
                'name' => $this->localized('name', null, $request),
                'slug' => $this->slug ?? null,
                'image' => $this->image,
            ];
        }

        return [
            'kind' => class_basename($this->resource),
            'id' => $this->id,
        ];
    }
}
