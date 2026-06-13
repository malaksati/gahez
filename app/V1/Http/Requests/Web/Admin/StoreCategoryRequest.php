<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\CategoryValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class StoreCategoryRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = CategoryValidation::store();
        $rules['image'] = ['nullable', 'image', 'max:2048'];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\StoreCategoryRequest)->messages();
    }
}
