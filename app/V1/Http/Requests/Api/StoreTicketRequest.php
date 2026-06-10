<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Concerns\ResolvesTicketAttachments;

class StoreTicketRequest extends ApiFormRequest
{
    use ResolvesTicketAttachments;

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
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            ...$this->ticketAttachmentRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'subject.required' => 'Please enter a subject for your ticket.',
            'subject.max' => 'Subject may not exceed 255 characters.',
            'description.required' => 'Please describe your issue.',
        ]);
    }
}
