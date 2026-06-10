<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\ProductValidation;

class StoreProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(ProductValidation::normalizeOptionalFields($this->all()));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return ProductValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'sku.unique' => 'This SKU is already in use.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'price.min' => 'Price must be at least 0.',
        ]);
    }
}
