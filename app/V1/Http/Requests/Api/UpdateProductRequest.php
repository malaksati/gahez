<?php

namespace App\V1\Http\Requests\Api;

use App\Models\Product;
use App\V1\Http\Requests\Rules\ProductValidation;

class UpdateProductRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $normalized = ProductValidation::normalizeOptionalFields($this->all());
        $productId = (int) $this->route('id');

        if (isset($normalized['slug']) && trim((string) $normalized['slug']) !== '') {
            $normalized['slug'] = Product::ensureUniqueSlug(
                (string) $normalized['slug'],
                $productId > 0 ? $productId : null,
            );
        }

        $this->merge($normalized);
    }
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return ProductValidation::update((int) $this->route('id'));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'sku.unique' => 'This SKU is already in use.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
        ]);
    }
}
