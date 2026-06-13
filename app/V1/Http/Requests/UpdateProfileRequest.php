<?php

namespace App\V1\Http\Requests;

use App\Models\User;
use App\V1\Http\Requests\Rules\PhoneValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId),
            ],
            'phone' => [
                'sometimes',
                ...PhoneValidation::rules(),
                Rule::unique(User::class)->ignore($userId),
            ],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'birthdate' => ['sometimes', 'nullable', 'date', 'before:today'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            $email = $this->has('email') ? $this->input('email') : $user->email;
            $phone = $this->has('phone') ? $this->input('phone') : $user->phone;

            if (empty($email) && empty($phone)) {
                $validator->errors()->add('email', __('messages.Provide email or phone'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower($this->string('email')->toString()),
            ]);
        }

        if ($this->has('remove_image')) {
            $this->merge([
                'remove_image' => $this->boolean('remove_image'),
            ]);
        }

        if ($this->has('birthdate') && $this->input('birthdate') === '') {
            $this->merge([
                'birthdate' => null,
            ]);
        }

        PhoneValidation::prepareRequest($this, ['phone']);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => PhoneValidation::message(),
        ];
    }
}
