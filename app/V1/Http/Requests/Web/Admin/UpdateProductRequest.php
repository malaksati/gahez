<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Concerns\ValidatesProductUnits;
use App\V1\Http\Requests\Concerns\ValidatesProductVariants;
use App\V1\Http\Requests\Rules\ProductValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends AdminFormRequest
{
    use ValidatesProductUnits, ValidatesProductVariants;

    protected function prepareForValidation(): void
    {
        $this->prepareProductPricingFields();

        $product = $this->route('product');
        $productId = is_object($product) ? $product->getKey() : (int) $product;

        $this->prepareProductSlugField($productId > 0 ? (int) $productId : null);
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
            ProductValidation::adminProductUnits(),
        );
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateProductVariants($validator);
        $this->validateProductUnits($validator);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateProductRequest)->messages();
    }
}
