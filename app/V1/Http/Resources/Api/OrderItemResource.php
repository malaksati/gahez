<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $productSnapshot = $this->product ? null : [
            'id' => $this->product_id,
            'name' => $this->product_name,
            'name_ar' => $this->product_name_ar,
            'slug' => $this->product_slug,
            'sku' => $this->product_sku,
            'is_snapshot' => true,
        ];

        $variantSnapshot = $this->variant ? null : ($this->variant_id ? [
            'id' => $this->variant_id,
            'name' => $this->variant_name,
            'name_ar' => $this->variant_name_ar,
            'sku' => $this->variant_sku,
            'is_snapshot' => true,
        ] : null);

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'product_name' => $this->product_name,
            'product_name_ar' => $this->product_name_ar,
            'product_slug' => $this->product_slug,
            'product_sku' => $this->product_sku,
            'variant_name' => $this->variant_name,
            'variant_name_ar' => $this->variant_name_ar,
            'variant_sku' => $this->variant_sku,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'line_discount' => (float) $this->line_discount,
            'note' => $this->note,
            'line_total' => round(
                ((float) $this->unit_price * (int) $this->quantity)
                - (float) $this->line_discount,
                2
            ),
            'product' => $this->product
                ? new ProductResource($this->whenLoaded('product'))
                : $productSnapshot,
            'variant' => $this->variant
                ? $this->whenLoaded('variant')
                : $variantSnapshot,
        ];
    }
}
