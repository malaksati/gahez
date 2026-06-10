<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\VariantValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateVariantRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return VariantValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateVariantRequest)->messages();
    }
}
