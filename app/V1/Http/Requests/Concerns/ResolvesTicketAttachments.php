<?php

namespace App\V1\Http\Requests\Concerns;

use Illuminate\Http\UploadedFile;

trait ResolvesTicketAttachments
{
    /**
     * @return array<string, list<string>>
     */
    protected function ticketAttachmentRules(): array
    {
        $fileRules = ['file', 'mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx', 'max:5120'];

        return [
            'attachments' => ['nullable', 'array'],
            'attachments.*' => $fileRules,
            'attachment' => ['nullable'],
            'attachment.*' => $fileRules,
        ];
    }

    /**
     * Accepts `attachments`, `attachments[0]`, `attachment`, or `attachment[0]` from multipart form-data.
     *
     * @return array<int, UploadedFile>|null
     */
    public function ticketAttachmentFiles(): ?array
    {
        $files = $this->collectUploadedFiles($this->file('attachments'));

        if ($files !== []) {
            return $files;
        }

        $files = $this->collectUploadedFiles($this->file('attachment'));

        return $files !== [] ? $files : null;
    }

    /**
     * @return list<UploadedFile>
     */
    private function collectUploadedFiles(mixed $files): array
    {
        if ($files === null) {
            return [];
        }

        if ($files instanceof UploadedFile) {
            return $files->isValid() ? [$files] : [];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn (mixed $file): bool => $file instanceof UploadedFile && $file->isValid(),
        ));
    }
}
