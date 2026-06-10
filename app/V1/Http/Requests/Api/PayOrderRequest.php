<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Services\OrderService;
use Illuminate\Validation\Rule;

class PayOrderRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', Rule::in(OrderService::PAYMENT_METHODS)],
        ];
    }
}
