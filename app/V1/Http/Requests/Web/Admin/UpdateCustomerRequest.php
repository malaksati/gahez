<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateCustomerRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage customers');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('customer')),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($this->route('customer')),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
