<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Concerns\ValidatesProductUnits;
use App\V1\Http\Requests\Concerns\ValidatesProductVariants;
use App\V1\Http\Requests\Rules\ProductValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreProductRequest extends AdminFormRequest
{
    use ValidatesProductUnits, ValidatesProductVariants;

    protected function prepareForValidation(): void
    {
        $this->prepareProductPricingFields();
        $this->prepareProductSlugField();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return array_merge(
            ProductValidation::store(),
            ProductValidation::adminMediaAndRelations(),
            ProductValidation::adminProductVariants(),
            ProductValidation::adminProductUnits(),
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\StoreProductRequest)->messages();
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateProductVariants($validator);
        $this->validateProductUnits($validator);
    }
}
