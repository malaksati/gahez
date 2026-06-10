<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateTicketRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'status' => ['sometimes', 'in:pending,resolved,closed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'subject.max' => 'Subject may not exceed 255 characters.',
            'status.in' => 'Status must be pending, resolved, or closed.',
        ]);
    }
}
