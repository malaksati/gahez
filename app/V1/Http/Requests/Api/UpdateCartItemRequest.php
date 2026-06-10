<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\CartItemValidation;

class UpdateCartItemRequest extends ApiFormRequest
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
        return CartItemValidation::updateQuantity();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'product_id.required' => 'Please specify the cart product.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'Please provide a quantity.',
        ]);
    }
}
