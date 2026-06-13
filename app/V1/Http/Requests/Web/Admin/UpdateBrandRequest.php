<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\BrandValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateBrandRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = BrandValidation::update();
        $rules['image'] = ['nullable', 'image', 'max:2048'];
        $rules['remove_image'] = ['sometimes', 'boolean'];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateBrandRequest)->messages();
    }
}
