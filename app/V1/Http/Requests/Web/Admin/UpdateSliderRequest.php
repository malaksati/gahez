<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\SliderValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateSliderRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return SliderValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateSliderRequest)->messages();
    }
}
