<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\BranchValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateBranchRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return BranchValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\UpdateBranchRequest)->messages();
    }
}
