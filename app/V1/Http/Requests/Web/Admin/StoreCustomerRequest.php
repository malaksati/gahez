<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Rules\Password;

class StoreCustomerRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage customers');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'birthdate' => ['nullable', 'date', 'before:today'],
        ];
    }
}
