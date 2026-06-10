<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\VariantOptionValidation;

class StoreVariantOptionRequest extends ApiFormRequest
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
        return VariantOptionValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'code.required' => 'Variant option code is required.',
            'variant_id.exists' => 'The selected variant does not exist.',
            'name.en.required' => 'English option name is required.',
        ]);
    }
}
