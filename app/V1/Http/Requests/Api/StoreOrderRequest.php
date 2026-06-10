<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Services\OrderService;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'use_wallet' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'string', Rule::in(OrderService::PAYMENT_METHODS)],
            'gift_offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'gift_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'item_notes' => ['sometimes', 'array'],
            'item_notes.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'item_notes.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'item_notes.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'address_id.required' => 'Please select a delivery address.',
            'address_id.exists' => 'The selected address is invalid.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'The selected payment method is invalid.',
            'use_wallet.prohibited_if' => 'Wallet cannot be used with cash on delivery.',
        ]);
    }
}
