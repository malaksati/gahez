<?php

namespace App\V1\Http\Requests\Concerns;

trait ResolvesSupportAttachments
{
    use ResolvesTicketAttachments;

    /**
     * @return array<string, list<string>>
     */
    protected function supportAttachmentRules(): array
    {
        return $this->ticketAttachmentRules();
    }

    /**
     * @return array<int, \Illuminate\Http\UploadedFile>|null
     */
    public function supportAttachmentFiles(): ?array
    {
        return $this->ticketAttachmentFiles();
    }
}
