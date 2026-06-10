<?php

namespace App\V1\Http\Resources\Api;

use App\V1\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $variant = $this->variant;
        $quantity = (int) $this->quantity;
        $offerService = app(OfferService::class);
        $pricing = $offerService->pricingForCartItem($this->resource);

        return [
            'id' => $this->id,
            'product' => new ProductResource($product),
            'variant' => $variant ? new ProductVariantResource($variant) : null,
            'product_unit' => $this->productUnit ? new ProductUnitResource($this->productUnit) : null,
            'quantity' => $quantity,
            'billable_quantity' => $pricing['billable_quantity'],
            'bonus_quantity' => max(0, $quantity - $pricing['billable_quantity']),
            'max_discounted_quantity' => $offerService->maxDiscountedQuantityForProduct($product),
            'discounted_quantity' => $pricing['discounted_quantity'],
            'full_price_quantity' => $pricing['full_price_quantity'],
            'discount' => $product->discount,
            'discount_type' => $product->discount_type,
            'unit_price' => $pricing['unit_price'],
            'subtotal' => $pricing['line_subtotal'],
        ];
    }
}
