<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\AddressValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

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
            'wallet' => ['nullable', 'numeric', 'min:0'],
            'points' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
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
        ]);
    }
}
