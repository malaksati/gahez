<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\BrandValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class StoreBrandRequest extends AdminFormRequest
{
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
        return (new \App\V1\Http\Requests\Api\StoreBrandRequest)->messages();
    }
}
