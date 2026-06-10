<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Concerns\ValidatesProductVariants;
use App\V1\Http\Requests\Rules\ProductValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends AdminFormRequest
{
    use ValidatesProductVariants;

    protected function prepareForValidation(): void
    {
        $this->merge(ProductValidation::normalizeOptionalFields($this->all()));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        $productId = is_object($product) ? $product->getKey() : (int) $product;

        return array_merge(
            ProductValidation::update($productId),
            ProductValidation::adminMediaAndRelations($productId),
            ProductValidation::adminProductVariants(),
        );
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateProductVariants($validator);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateProductRequest)->messages();
    }
}
