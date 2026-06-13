<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\PhoneValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateAdminUserRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $userId = $this->route('admin_user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$userId],
            'phone' => PhoneValidation::rules(),
            'birthdate' => ['nullable', 'date', 'before:today'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    protected function prepareForValidation(): void
    {
        PhoneValidation::prepareRequest($this, ['phone']);

        if ($this->has('birthdate') && $this->input('birthdate') === '') {
            $this->merge(['birthdate' => null]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.The name field is required.'),
            'email.required' => __('messages.The email field is required.'),
            'email.unique' => __('messages.This email is already in use.'),
            'password.min' => __('messages.Password must be at least 8 characters.'),
            'password.confirmed' => __('messages.Password confirmation does not match.'),
            'phone.regex' => PhoneValidation::message(),
        ];
    }
}
