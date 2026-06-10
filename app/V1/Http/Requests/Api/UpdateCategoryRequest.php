<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\CategoryValidation;

class UpdateCategoryRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return CategoryValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.array' => 'Category name must be provided as translations.',
            'name.en.max' => 'English category name may not exceed 255 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ]);
    }
}
