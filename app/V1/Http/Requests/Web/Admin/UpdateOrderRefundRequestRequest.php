<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateOrderRefundRequestRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,approved,rejected'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'status.in' => 'Status must be pending, approved, or rejected.',
        ]);
    }
}
