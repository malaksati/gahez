<?php

namespace App\V1\DataTransfer\Imports;

use App\Models\DataTransferBatch;
use App\Models\Variant;
use App\Models\VariantOption;
use App\V1\DataTransfer\Concerns\LogsImportFailures;
use App\V1\DataTransfer\DataTransferConfig;
use App\V1\DataTransfer\Support\ImportRelationResolver;
use App\V1\Http\Requests\Rules\VariantOptionValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VariantOptionImporter
{
    use LogsImportFailures;

    public function __construct(
        protected SpreadsheetReader $reader,
        protected VariantOptionRowMapper $mapper,
    ) {}

    public function import(DataTransferBatch $batch, string $absolutePath): void
    {
        ImportRelationResolver::resetCaches();

        $rows = $this->reader->readRows($absolutePath);

        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'total_rows' => count($rows),
            'started_at' => now(),
        ]);

        $buffer = [];
        $processed = 0;
        $success = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $rowNumber = (int) ($row['_spreadsheet_row'] ?? ($processed + 2));
            unset($row['_spreadsheet_row']);

            try {
                $payload = $this->mapper->toPayload($row);
                $providedId = $payload['id'] ?? null;
                $existingId = $providedId
                    ? VariantOption::withTrashed()->whereKey($providedId)->value('id')
                    : null;

                if ($providedId && ! $existingId) {
                    if (ImportRelationResolver::shouldSkipMissingRelations()) {
                        unset($payload['id']);
                        $existingId = null;
                    } else {
                        $failed++;
                        $this->logValidationFailure(
                            $batch,
                            $rowNumber,
                            $row,
                            Validator::make(
                                ['id' => null],
                                ['id' => 'required'],
                                ['id.required' => __('validation.exists', ['attribute' => 'id'])],
                            ),
                        );
                        $processed++;

                        continue;
                    }
                }

                if ($payload['variant_id'] && ! Variant::query()->whereKey($payload['variant_id'])->exists()) {
                    if (ImportRelationResolver::shouldSkipMissingRelations()) {
                        $processed++;

                        continue;
                    }

                    $failed++;
                    $this->logValidationFailure(
                        $batch,
                        $rowNumber,
                        $row,
                        Validator::make(
                            ['variant_id' => null],
                            ['variant_id' => 'required'],
                            ['variant_id.required' => __('validation.exists', ['attribute' => 'variant_id'])],
                        ),
                    );
                    $processed++;

                    continue;
                }

                $rules = $existingId
                    ? VariantOptionValidation::update()
                    : VariantOptionValidation::store();

                if ($existingId) {
                    $rules['code'] = ['sometimes', 'string', 'max:50', Rule::unique('variant_options', 'code')->ignore($existingId)];
                }

                $validator = Validator::make(
                    collect($payload)->except('id')->all(),
                    $rules,
                );

                if ($validator->fails()) {
                    $failed++;
                    $this->logValidationFailure($batch, $rowNumber, $row, $validator);
                } else {
                    $buffer[] = $this->prepareRecord($payload);
                    $success++;
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->logRowException($batch, $rowNumber, $row, $exception);
            }

            $processed++;

            if (count($buffer) >= $this->chunkSize()) {
                [$chunkFailed, $chunkSuccess] = $this->flushBuffer($buffer);
                $failed += $chunkFailed;
                $success -= $chunkSuccess;
                $buffer = [];
            }

            if ($processed % 50 === 0) {
                $batch->update([
                    'processed_rows' => $processed,
                    'success_count' => $success,
                    'failed_count' => $failed,
                ]);
            }
        }

        if ($buffer !== []) {
            [$chunkFailed, $chunkSuccess] = $this->flushBuffer($buffer);
            $failed += $chunkFailed;
            $success -= $chunkSuccess;
        }

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'processed_rows' => $processed,
            'success_count' => $success,
            'failed_count' => $failed,
            'completed_at' => now(),
            'message' => __('messages.Import finished with :success successes and :failed failures.', [
                'success' => $success,
                'failed' => $failed,
            ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function prepareRecord(array $payload): array
    {
        $now = now();

        return [
            'code' => $payload['code'],
            'variant_id' => $payload['variant_id'],
            'name' => json_encode($payload['name'], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $buffer
     * @return array{0: int, 1: int}
     */
    private function flushBuffer(array $buffer): array
    {
        try {
            DB::transaction(function () use ($buffer) {
                VariantOption::query()->upsert(
                    $buffer,
                    ['code'],
                    ['variant_id', 'name', 'updated_at'],
                );
            });

            return [0, 0];
        } catch (\Throwable $exception) {
            Log::error('Variant option import chunk failed', [
                'rows' => count($buffer),
                'message' => $exception->getMessage(),
            ]);

            return [count($buffer), count($buffer)];
        }
    }

    private function chunkSize(): int
    {
        return (int) config('data-transfer.chunk_size', DataTransferConfig::CHUNK_SIZE);
    }
}
