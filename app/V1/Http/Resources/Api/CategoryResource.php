<?php

namespace App\V1\Http\Resources\Api;

use App\V1\Http\Resources\Concerns\LocalizesTranslatableAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    use LocalizesTranslatableAttributes;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized('name', null, $request),
            'image' => $this->image,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'sort_order' => (int) ($this->sort_order ?? 0),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
