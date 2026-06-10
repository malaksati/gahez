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
        return CategoryValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\StoreCategoryRequest)->messages();
    }
}
