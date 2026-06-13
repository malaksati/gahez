<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\BranchValidation;
use App\V1\Http\Requests\Rules\PhoneValidation;

class StoreBranchRequest extends ApiFormRequest
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
        return BranchValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.en.required' => 'English branch name is required.',
            'address.required' => 'Please enter the branch address.',
            'latitude.required' => 'Please provide a latitude for the branch.',
        ]);
    }

    protected function prepareForValidation(): void
    {
        PhoneValidation::prepareRequest($this, ['phone']);
    }
}
