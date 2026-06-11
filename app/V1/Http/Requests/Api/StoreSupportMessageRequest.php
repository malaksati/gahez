<?php

namespace App\V1\Http\Requests\Api;

use App\Models\Support;
use App\V1\Http\Requests\Concerns\ResolvesSupportAttachments;

class StoreSupportMessageRequest extends ApiFormRequest
{
    use ResolvesSupportAttachments;

    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        $support = $this->route('support');

        if (! $support instanceof Support) {
            $support = Support::query()->find($support);
        }

        return $support instanceof Support && $this->user()->can('sendMessage', $support);
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
            ...$this->supportAttachmentRules(),
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
        ]);
    }
}
