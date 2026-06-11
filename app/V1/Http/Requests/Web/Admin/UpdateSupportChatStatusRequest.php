<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Rule;

class UpdateSupportChatStatusRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['open', 'closed'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be open or closed.',
        ]);
    }
}
