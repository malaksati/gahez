<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\SliderValidation;

class StoreSliderRequest extends ApiFormRequest
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
        return SliderValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'image.required' => 'Please upload a slider image.',
            'image.image' => 'The slider file must be an image.',
            'image.max' => 'The slider image may not be larger than 4 MB.',
        ]);
    }
}
