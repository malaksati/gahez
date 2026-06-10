<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\BranchValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class StoreBranchRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return BranchValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new \App\V1\Http\Requests\Api\StoreBranchRequest)->messages();
    }
}
