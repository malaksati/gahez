<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\BrandValidation;

class UpdateBrandRequest extends ApiFormRequest
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
        return BrandValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.array' => 'Brand name must be provided as translations.',
            'name.en.max' => 'English brand name may not exceed 255 characters.',
            'name.ar.max' => 'Arabic brand name may not exceed 255 characters.',
        ]);
    }
}
