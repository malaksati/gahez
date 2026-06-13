<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\V1\Http\Requests\Rules\PhoneValidation;
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
                ...PhoneValidation::rules(),
                Rule::unique(User::class),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower($this->input('email')),
            ]);
        }

        PhoneValidation::prepareRequest($this, ['phone']);
    }
}
