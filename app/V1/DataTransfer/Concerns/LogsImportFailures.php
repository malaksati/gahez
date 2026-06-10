<?php

namespace App\V1\DataTransfer\Concerns;

use App\Models\DataTransferBatch;
use App\Models\ImportRowLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

trait LogsImportFailures
{
    protected function logValidationFailure(
        DataTransferBatch $batch,
        int $rowNumber,
        array $rowData,
        Validator $validator,
    ): void {
        $errors = $validator->errors()->toArray();

        ImportRowLog::query()->create([
            'data_transfer_batch_id' => $batch->id,
            'row_number' => $rowNumber,
            'row_data' => $rowData,
            'errors' => $errors,
        ]);

        Log::warning('Import row validation failed', [
            'batch_id' => $batch->id,
            'entity' => $batch->entity,
            'row_number' => $rowNumber,
            'errors' => $errors,
            'sku' => $rowData['sku'] ?? null,
            'slug' => $rowData['slug'] ?? null,
        ]);
    }

    protected function logRowException(
        DataTransferBatch $batch,
        int $rowNumber,
        array $rowData,
        \Throwable $exception,
    ): void {
        ImportRowLog::query()->create([
            'data_transfer_batch_id' => $batch->id,
            'row_number' => $rowNumber,
            'row_data' => $rowData,
            'errors' => ['exception' => [$exception->getMessage()]],
        ]);

        Log::error('Import row failed', [
            'batch_id' => $batch->id,
            'entity' => $batch->entity,
            'row_number' => $rowNumber,
            'message' => $exception->getMessage(),
        ]);
    }
}
