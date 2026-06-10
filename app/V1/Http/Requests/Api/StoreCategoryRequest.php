<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\CategoryValidation;

class StoreCategoryRequest extends ApiFormRequest
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
        return CategoryValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.required' => 'Category name translations are required.',
            'name.en.required' => 'English category name is required.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ]);
    }
}
