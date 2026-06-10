<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Concerns\ResolvesTicketAttachments;
use App\V1\Http\Requests\Web\AdminFormRequest;

class StoreTicketMessageRequest extends AdminFormRequest
{
    use ResolvesTicketAttachments;

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            ...$this->ticketAttachmentRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'message.required' => 'Message is required.',
            'attachments.*.mimes' => 'Each attachment must be jpeg, png, jpg, gif, webp, pdf, doc, or docx.',
            'attachments.*.max' => 'Each attachment may not be larger than 5MB.',
        ]);
    }
}
