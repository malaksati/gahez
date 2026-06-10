<?php

namespace App\V1\Http\Requests\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required_without:phone',
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'birthdate' => ['nullable', 'date', 'before:today'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower($this->input('email')),
            ]);
        }

        if ($this->has('birthdate') && $this->input('birthdate') === '') {
            $this->merge([
                'birthdate' => null,
            ]);
        }
    }
}
