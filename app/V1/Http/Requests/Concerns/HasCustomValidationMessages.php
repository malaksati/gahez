<?php

namespace App\V1\Http\Requests\Concerns;

trait HasCustomValidationMessages
{
    /**
     * @param  array<string, string>  $messages
     * @return array<string, string>
     */
    protected function mergeMessages(array $messages): array
    {
        return array_merge($this->defaultMessages(), $messages);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultMessages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'integer' => 'The :attribute must be an integer.',
            'numeric' => 'The :attribute must be a number.',
            'boolean' => 'The :attribute must be true or false.',
            'email' => 'The :attribute must be a valid email address.',
            'max.string' => 'The :attribute may not be greater than :max characters.',
            'min.numeric' => 'The :attribute must be at least :min.',
            'exists' => 'The selected :attribute is invalid.',
            'unique' => 'The :attribute has already been taken.',
            'in' => 'The selected :attribute is invalid.',
            'array' => 'The :attribute must be an array.',
            'date' => 'The :attribute must be a valid date.',
            'image' => 'The :attribute must be an image.',
            'mimes' => 'The :attribute must be a file of type: :values.',
        ];
    }
}
