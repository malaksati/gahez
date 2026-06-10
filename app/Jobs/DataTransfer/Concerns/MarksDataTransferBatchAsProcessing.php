<?php

namespace App\Jobs\DataTransfer\Concerns;

use App\Models\DataTransferBatch;

trait MarksDataTransferBatchAsProcessing
{
    protected function markBatchProcessing(DataTransferBatch $batch, string $message): void
    {
        if ($batch->status !== DataTransferBatch::STATUS_PENDING) {
            return;
        }

        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'started_at' => now(),
            'message' => $message,
        ]);
    }
}
