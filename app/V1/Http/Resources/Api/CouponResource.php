<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'discount_value' => (float) $this->discount_value,
            'min_cart_amount' => $this->min_cart_amount !== null ? (float) $this->min_cart_amount : null,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'usage_limit' => $this->usage_limit,
            'total_orders_used' => isset($this->orders_count) ? (int) $this->orders_count : $this->totalOrdersUsed(),
            'first_order_only' => (bool) $this->first_order_only,
            'grants_free_delivery' => $this->grantsFreeDelivery(),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'is_valid' => $this->isValid(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
