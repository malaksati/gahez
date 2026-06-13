<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\AddressValidation;
use App\V1\Http\Requests\Rules\PhoneValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

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
                ...PhoneValidation::rules(),
                Rule::unique('users')->ignore($this->route('customer')),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'wallet' => ['nullable', 'numeric', 'min:0'],
            'points' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_verified' => ['nullable', 'boolean'],
            ...AddressValidation::nestedForCustomer(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $email = $this->input('email');
            $phone = $this->input('phone');

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

        if ($this->has('birthdate') && $this->input('birthdate') === '') {
            $this->merge(['birthdate' => null]);
        }

        $address = $this->input('address');

        if (is_array($address)) {
            $this->merge([
                'address' => array_merge($address, [
                    'is_default' => $this->boolean('address.is_default'),
                ]),
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_verified' => $this->boolean('is_verified'),
            'remove_image' => $this->boolean('remove_image'),
        ]);

        PhoneValidation::prepareRequest($this, ['phone']);
        PhoneValidation::prepareNested($this, 'address', 'phone');
    }
}
