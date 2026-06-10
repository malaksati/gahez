<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\CartItemValidation;

class StoreCartItemRequest extends ApiFormRequest
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
        return CartItemValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'product_id.required' => 'Please select a product to add to the cart.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.min' => 'Quantity must be at least 1.',
        ]);
    }
}
