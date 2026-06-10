<?php

namespace App\Jobs\DataTransfer;

use App\Jobs\DataTransfer\Concerns\MarksDataTransferBatchAsProcessing;
use App\Models\DataTransferBatch;
use App\V1\DataTransfer\Imports\ProductImporter;
use App\V1\Services\DataTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, MarksDataTransferBatchAsProcessing, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(
        public int $batchId,
    ) {}

    public function handle(ProductImporter $importer, DataTransferService $dataTransfer): void
    {
        $batch = DataTransferBatch::query()->findOrFail($this->batchId);

        try {
            $this->markBatchProcessing($batch, __('messages.Import job reading spreadsheet.'));
            $importer->import($batch, $dataTransfer->resolveImportAbsolutePath($batch));
        } catch (\Throwable $exception) {
            Log::error('Product import job failed', [
                'batch_id' => $batch->id,
                'message' => $exception->getMessage(),
            ]);

            $batch->update([
                'status' => DataTransferBatch::STATUS_FAILED,
                'completed_at' => now(),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
