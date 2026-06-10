<?php

namespace App\V1\Http\Requests\Web\Admin;

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
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
        ];
    }
}
