<?php

namespace App\V1\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'status' => $this->status,
            'total' => $this->total,
            'sub_total' => $this->sub_total,
            'order_discount' => $this->order_discount,
            'coupon_discount' => $this->coupon_discount,
            'total_shipping' => $this->total_shipping,
            'total_commission' => $this->total_commission,
            'refunded_total' => $this->refunded_total,
            'refund_status' => $this->refund_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
            'notes' => $this->notes,
            'shipping_day' => $this->shipping_day,
            'is_fast_shipping' => (bool) $this->is_fast_shipping,
            'fast_shipping_fee' => (float) $this->fast_shipping_fee,
            'wallet_used' => (float) $this->wallet_used,
            'cancellation_reason' => $this->cancellation_reason,
            'cashback_awarded_at' => $this->cashback_awarded_at?->toIso8601String(),
            'address_id' => $this->address_id,
            'shipping_address_snapshot' => $this->shipping_address_snapshot,
            'address' => $this->address
                ? new AddressResource($this->whenLoaded('address'))
                : $this->shipping_address_snapshot,
            'user' => new UserResource($this->whenLoaded('user')),
            'coupon' => $this->coupon,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
