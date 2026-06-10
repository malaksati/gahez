<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\VariantOptionValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateVariantOptionRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return VariantOptionValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateVariantOptionRequest)->messages();
    }
}
