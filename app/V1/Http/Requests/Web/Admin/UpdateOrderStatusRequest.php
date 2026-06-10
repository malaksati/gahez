<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateOrderStatusRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,processing,ready_for_delivery,shipped,delivered,cancelled,refunded'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');
            $order = $this->route('order');

            if (in_array($status, ['shipped', 'delivered']) && empty($order->shipping_address_snapshot)) {
                $validator->errors()->add('status', __('messages.Cannot change status to shipped without a shipping address.'));
            }
        });
    }
}

