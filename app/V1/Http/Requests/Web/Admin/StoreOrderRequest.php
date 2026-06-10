<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;
use App\V1\Services\OrderService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('customer_type') === 'existing' && $this->filled('address_id')) {
            $this->merge(['address_mode' => 'existing']);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string|\Illuminate\Contracts\Validation\ValidationRule>>
     */
    public function rules(): array
    {
        $rules = [
            'customer_type' => ['required', 'in:existing,new'],
            'address_mode' => ['nullable', 'in:existing,new'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string'],
            'payment_method' => ['required', 'string', Rule::in(OrderService::PAYMENT_METHODS)],
            'payment_status' => ['required', 'string'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];

        if ($this->input('customer_type') === 'existing') {
            $rules['user_id'] = ['required', 'exists:users,id'];
        } else {
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_email'] = ['nullable', 'string', 'email', 'max:255', 'unique:users,email'];
            $rules['customer_phone'] = ['nullable', 'string', 'max:255', 'unique:users,phone'];
        }

        $addressMode = $this->input('address_mode');

        if ($this->input('customer_type') === 'existing' && $this->filled('address_id')) {
            $addressMode = 'existing';
        }

        $needsNewAddress = $this->input('customer_type') === 'new'
            || $addressMode === 'new';

        if ($needsNewAddress) {
            $rules['address_name'] = ['required', 'string', 'max:255'];
            $rules['address'] = ['required', 'string', 'max:500'];
            $rules['latitude'] = ['required', 'string', 'max:32'];
            $rules['longitude'] = ['required', 'string', 'max:32'];
            $rules['address_phone'] = ['nullable', 'string', 'max:50'];
            $rules['city'] = ['nullable', 'string', 'max:120'];
            $rules['state'] = ['nullable', 'string', 'max:120'];
        } elseif ($addressMode === 'existing') {
            $rules['address_id'] = [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    if ($this->input('customer_type') === 'existing' && $this->filled('user_id')) {
                        $query->where('user_id', $this->input('user_id'));
                    }
                }),
            ];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('customer_type') === 'new') {
                if (empty($this->input('customer_email')) && empty($this->input('customer_phone'))) {
                    $validator->errors()->add('customer_email', __('messages.Provide email or phone'));
                }
            }
        });
    }
}
