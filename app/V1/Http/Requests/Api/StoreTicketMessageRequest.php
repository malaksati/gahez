<?php

namespace App\V1\Http\Requests\Api;

use App\Models\Ticket;
use App\V1\Http\Requests\Concerns\ResolvesTicketAttachments;

class StoreTicketMessageRequest extends ApiFormRequest
{
    use ResolvesTicketAttachments;

    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        $ticket = Ticket::query()->find($this->route('id'));

        return $ticket !== null && $ticket->user_id === $this->user()->id;
    }

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
            'message.required' => 'Please enter a message.',
            'attachments.*.mimes' => 'Each attachment must be jpeg, png, jpg, gif, webp, pdf, doc, or docx.',
            'attachments.*.max' => 'Each attachment may not be larger than 5MB.',
            'attachment.*.mimes' => 'Each attachment must be jpeg, png, jpg, gif, webp, pdf, doc, or docx.',
            'attachment.*.max' => 'Each attachment may not be larger than 5MB.',
        ]);
    }
}
