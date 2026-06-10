<?php

namespace App\V1\DataTransfer\Imports;

use App\Models\DataTransferBatch;
use App\Models\ImportRowLog;
use App\V1\DataTransfer\Concerns\LogsImportFailures;
use App\V1\DataTransfer\Support\ImportDuplicateGuard;
use App\V1\Http\Requests\Rules\VariantValidation;
use App\V1\Services\VariantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VariantImporter
{
    use LogsImportFailures;

    public function __construct(
        protected SpreadsheetReader $reader,
        protected VariantRowMapper $mapper,
        protected VariantService $variants,
    ) {}

    public function import(DataTransferBatch $batch, string $absolutePath): void
    {
        $rows = $this->reader->readRows($absolutePath);

        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'total_rows' => count($rows),
            'started_at' => $batch->started_at ?? now(),
            'message' => __('messages.Import job processing rows.'),
        ]);

        /** @var array<string, array{variant: array<string, mixed>, options: list<array<string, mixed>>, rows: list<array{number: int, data: array<string, mixed>}>}> $groups */
        $groups = [];
        $duplicateGuard = new ImportDuplicateGuard;
        $processed = 0;
        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $rowNumber = (int) ($row['_spreadsheet_row'] ?? ($processed + 2));
            unset($row['_spreadsheet_row']);

            try {
                $mapped = $this->mapper->toImportRow($row);

                if ($duplicateGuard->shouldSkipVariant($mapped['variant'])) {
                    $skipped++;
                } elseif (
                    $mapped['option'] !== null
                    && $duplicateGuard->shouldSkipVariantOption($mapped['option']['code'] ?? null)
                ) {
                    $skipped++;
                } else {
                    $validator = Validator::make(
                        $this->validationData($mapped),
                        VariantValidation::importRow($mapped),
                    );

                    if ($validator->fails()) {
                        $failed++;
                        $this->logValidationFailure($batch, $rowNumber, $row, $validator);
                    } else {
                        $groupKey = $this->groupKey($mapped['variant']);
                        $this->appendToGroup($groups, $groupKey, $mapped, $rowNumber, $row);
                        $success++;
                    }
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->logRowException($batch, $rowNumber, $row, $exception);
            }

            $processed++;

            if ($processed % 50 === 0) {
                $batch->update([
                    'processed_rows' => $processed,
                    'success_count' => $success,
                    'failed_count' => $failed,
                    'skipped_count' => $skipped,
                ]);
            }
        }

        foreach ($groups as $group) {
            [$groupFailed, $groupSuccess] = $this->persistGroup($batch, $group);
            $failed += $groupFailed;
            $success -= $groupSuccess;
        }

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'processed_rows' => $processed,
            'success_count' => max(0, $success),
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'completed_at' => now(),
            'message' => $this->finishedMessage(max(0, $success), $failed, $skipped),
        ]);
    }

    /**
     * @param  array{variant: array<string, mixed>, options: list<array<string, mixed>>, rows: list<array{number: int, data: array<string, mixed>}>}  $group
     * @return array{0: int, 1: int}
     */
    private function persistGroup(DataTransferBatch $batch, array $group): array
    {
        $rowCount = count($group['rows']);

        try {
            DB::transaction(function () use ($group) {
                $variantPayload = $group['variant'];
                unset($variantPayload['id']);

                $options = $this->normalizeOptions($group['options']);

                $this->variants->create([
                    ...$variantPayload,
                    'options' => $options,
                ]);
            });

            return [0, 0];
        } catch (\Throwable $exception) {
            Log::error('Variant import group failed', [
                'batch_id' => $batch->id,
                'variant_id' => $group['variant']['id'] ?? null,
                'rows' => $rowCount,
                'message' => $exception->getMessage(),
            ]);

            foreach ($group['rows'] as $rowMeta) {
                ImportRowLog::query()->create([
                    'data_transfer_batch_id' => $batch->id,
                    'row_number' => $rowMeta['number'],
                    'row_data' => $rowMeta['data'],
                    'errors' => ['exception' => [$exception->getMessage()]],
                ]);
            }

            return [$rowCount, $rowCount];
        }
    }

    /**
     * @param  array<string, array{variant: array<string, mixed>, options: list<array<string, mixed>>, rows: list<array{number: int, data: array<string, mixed>}>}>  $groups
     * @param  array{variant: array<string, mixed>, option: ?array<string, mixed>}  $mapped
     * @param  array<string, mixed>  $row
     */
    private function appendToGroup(
        array &$groups,
        string $groupKey,
        array $mapped,
        int $rowNumber,
        array $row,
    ): void {
        if (! isset($groups[$groupKey])) {
            $groups[$groupKey] = [
                'variant' => $mapped['variant'],
                'options' => [],
                'rows' => [],
            ];
        } else {
            $groups[$groupKey]['variant'] = array_merge($groups[$groupKey]['variant'], $mapped['variant']);
        }

        if ($mapped['option'] !== null) {
            $groups[$groupKey]['options'][] = $mapped['option'];
        }

        $groups[$groupKey]['rows'][] = [
            'number' => $rowNumber,
            'data' => $row,
        ];
    }

    private function groupKey(array $variant): string
    {
        return 'name:'.mb_strtolower(trim((string) ($variant['name']['en'] ?? '')));
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @return list<array<string, mixed>>
     */
    private function normalizeOptions(array $options): array
    {
        return array_values(array_map(function (array $option) {
            $code = trim((string) ($option['code'] ?? ''));

            return [
                'id' => $option['id'] ?? null,
                'name' => $option['name'],
                'code' => $code !== '' ? $code : null,
            ];
        }, $options));
    }

    /**
     * @param  array{variant: array<string, mixed>, option: ?array<string, mixed>}  $mapped
     * @return array<string, mixed>
     */
    private function validationData(array $mapped): array
    {
        $data = [
            'name' => $mapped['variant']['name'],
            'is_required' => $mapped['variant']['is_required'],
            'is_active' => $mapped['variant']['is_active'],
        ];

        if ($mapped['option'] !== null) {
            $data['options'] = [$mapped['option']];
        }

        return $data;
    }

    private function finishedMessage(int $success, int $failed, int $skipped): string
    {
        if ($skipped > 0) {
            return __('messages.Import finished with :success successes, :skipped skipped, and :failed failures.', [
                'success' => $success,
                'skipped' => $skipped,
                'failed' => $failed,
            ]);
        }

        return __('messages.Import finished with :success successes and :failed failures.', [
            'success' => $success,
            'failed' => $failed,
        ]);
    }
}
