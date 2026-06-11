<?php

namespace App\V1\Http\Requests\Api;

use App\Models\Support;
use App\V1\Http\Requests\Concerns\ResolvesSupportAttachments;

class StoreSupportChatRequest extends ApiFormRequest
{
    use ResolvesSupportAttachments;

    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Support::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            ...$this->supportAttachmentRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'subject.max' => 'Subject may not exceed 255 characters.',
            'attachments.*.mimes' => 'Each attachment must be jpeg, png, jpg, gif, webp, pdf, doc, or docx.',
            'attachments.*.max' => 'Each attachment may not be larger than 5MB.',
        ]);
    }
}
