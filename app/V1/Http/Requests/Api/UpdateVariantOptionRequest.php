<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\VariantOptionValidation;

class UpdateVariantOptionRequest extends ApiFormRequest
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
        return VariantOptionValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'code.max' => 'Variant option code may not exceed 50 characters.',
            'variant_id.exists' => 'The selected variant does not exist.',
            'name.en.max' => 'English option name may not exceed 255 characters.',
        ]);
    }
}
