<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\BranchValidation;

class UpdateBranchRequest extends ApiFormRequest
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
        return BranchValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'address.max' => 'Branch address may not exceed 500 characters.',
            'latitude.max' => 'Latitude may not exceed 32 characters.',
            'longitude.max' => 'Longitude may not exceed 32 characters.',
        ]);
    }
}
