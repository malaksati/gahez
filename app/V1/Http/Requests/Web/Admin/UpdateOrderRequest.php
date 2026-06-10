<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;
use App\V1\Services\OrderService;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $allowedMethods = OrderService::PAYMENT_METHODS;
        $currentMethod = $this->route('order')?->payment_method;

        if (is_string($currentMethod) && $currentMethod !== '' && ! in_array($currentMethod, $allowedMethods, true)) {
            $allowedMethods[] = $currentMethod;
        }

        return [
            'status' => ['sometimes', 'in:pending,processing,ready_for_delivery,cancelled,refunded'],
            'payment_status' => ['sometimes', 'in:pending,paid,failed,refunded'],
            'payment_method' => [
                'nullable',
                'string',
                'max:50',
                Rule::in($allowedMethods),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'shipping_name' => ['nullable', 'string', 'max:255'],
            'shipping_phone' => ['nullable', 'string', 'max:50'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'shipping_city' => ['nullable', 'string', 'max:255'],
            'shipping_state' => ['nullable', 'string', 'max:255'],
            'remove_address' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'status.required' => 'Order status is required.',
            'status.in' => 'Invalid order status.',
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');
            $order = $this->route('order');
            $removeAddress = $this->boolean('remove_address');

            $finalStatus = $status ?? $order->status;

            if (in_array($finalStatus, ['ready_for_delivery'])) {
                $hasAddress = ! empty($order->shipping_address_snapshot);

                if ($removeAddress) {
                    $hasAddress = false;
                } elseif (! $hasAddress) {
                    $isAdding = ! empty($this->input('shipping_name')) && ! empty($this->input('shipping_address'));
                    $hasAddress = $isAdding;
                }

                if (! $hasAddress) {
                    $validator->errors()->add('status', __('messages.Cannot change status to ready for delivery without a shipping address.'));
                }
            }
        });
    }
}
