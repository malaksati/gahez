<?php

namespace App\V1\Http\Requests\Api\Auth;

use App\V1\Http\Requests\Api\ApiFormRequest;
use Illuminate\Validation\Rules\Password;

class SetNewPasswordRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $resetToken = $this->input('reset_token') ?? $this->input('token');

        if ($resetToken === null && is_array($this->input('data'))) {
            $resetToken = $this->input('data.reset_token') ?? $this->input('data.token');
        }

        $password = $this->input('password') ?? $this->input('new_password');
        $passwordConfirmation = $this->input('password_confirmation')
            ?? $this->input('new_password_confirmation')
            ?? $this->input('confirm_password');

        $merge = [];

        if ($resetToken !== null && $resetToken !== '') {
            $merge['reset_token'] = $resetToken;
        }

        if ($password !== null && $password !== '') {
            $merge['password'] = $password;
        }

        if ($passwordConfirmation !== null && $passwordConfirmation !== '') {
            $merge['password_confirmation'] = $passwordConfirmation;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reset_token' => 'reset token',
        ];
    }
}
