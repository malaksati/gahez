<?php

namespace App\Jobs\DataTransfer;

use App\Models\DataTransferBatch;
use App\V1\Services\DataTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $batchId,
        public array $filters = [],
    ) {}

    public function handle(DataTransferService $dataTransfer): void
    {
        $batch = DataTransferBatch::query()->findOrFail($this->batchId);

        try {
            $dataTransfer->runVariantExport($batch, $this->filters);
        } catch (\Throwable $exception) {
            Log::error('Variant export job failed', ['batch_id' => $batch->id, 'message' => $exception->getMessage()]);
            $batch->update(['status' => DataTransferBatch::STATUS_FAILED, 'completed_at' => now(), 'message' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
