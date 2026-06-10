<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\Models\User;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public function rules(): array
    {
        $userType = $this->input('user_type', 'new');

        $rules = [
            'user_type' => ['nullable', 'string', Rule::in(['new', 'existing'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];

        if ($userType === 'existing') {
            $rules['user_id'] = [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::query()->find($value);
                    if ($user && $user->hasRole(['admin', 'super-admin'])) {
                        $fail(__('messages.This user is already an admin.'));
                    }
                },
            ];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
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
            'password.required' => __('messages.The password field is required.'),
            'password.min' => __('messages.Password must be at least 8 characters.'),
            'password.confirmed' => __('messages.Password confirmation does not match.'),
            'user_id.required' => __('messages.Please select a user to link.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_type' => $this->input('user_type', 'new'),
        ]);
    }
}
