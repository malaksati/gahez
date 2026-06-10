<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\BrandValidation;

class StoreBrandRequest extends ApiFormRequest
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
        return BrandValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.required' => 'Brand name translations are required.',
            'name.en.required' => 'English brand name is required.',
            'name.ar.required' => 'Arabic brand name is required.',
        ]);
    }
}
