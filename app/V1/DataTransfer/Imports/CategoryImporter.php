<?php

namespace App\V1\DataTransfer\Imports;

use App\Models\Category;
use App\Models\DataTransferBatch;
use App\V1\DataTransfer\Concerns\LogsImportFailures;
use App\V1\DataTransfer\DataTransferConfig;
use App\V1\DataTransfer\Support\ImportDuplicateGuard;
use App\V1\DataTransfer\Support\ImportRelationResolver;
use App\V1\Http\Requests\Rules\CategoryValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryImporter
{
    use LogsImportFailures;

    public function __construct(
        protected SpreadsheetReader $reader,
        protected CategoryRowMapper $mapper,
    ) {}

    public function import(DataTransferBatch $batch, string $absolutePath): void
    {
        ImportRelationResolver::resetCaches();

        $rows = $this->reader->readRows($absolutePath);

        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'total_rows' => count($rows),
            'started_at' => $batch->started_at ?? now(),
            'message' => __('messages.Import job processing rows.'),
        ]);

        $buffer = [];
        $pendingParentLinks = [];
        /** @var array<string, true> $insertedSlugs */
        $insertedSlugs = [];
        $duplicateGuard = new ImportDuplicateGuard;
        $processed = 0;
        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $rowNumber = (int) ($row['_spreadsheet_row'] ?? ($processed + 2));
            unset($row['_spreadsheet_row']);

            try {
                $payload = $this->mapper->toPayload($row);

                if ($duplicateGuard->shouldSkipCategory($payload)) {
                    $skipped++;
                } else {

                    $validator = Validator::make($payload, CategoryValidation::import());

                    if ($validator->fails()) {
                        $failed++;
                        $this->logValidationFailure($batch, $rowNumber, $row, $validator);
                    } else {
                        $buffer[] = $this->prepareRecord($payload);
                        $insertedSlugs[$payload['slug']] = true;
                        $success++;

                        $parentReference = $this->mapper->extractParentReference($row);
                        $explicitParentId = $this->mapper->extractExplicitParentId($row);

                        if ($parentReference !== null || $explicitParentId !== null) {
                            $pendingParentLinks[] = [
                                'child_slug' => $payload['slug'],
                                'parent_reference' => $parentReference,
                                'parent_id' => $explicitParentId,
                                'row_number' => $rowNumber,
                                'row' => $row,
                            ];
                        }
                    }
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
                    'skipped_count' => $skipped,
                ]);
            }
        }

        if ($buffer !== []) {
            [$chunkFailed, $chunkSuccess] = $this->flushBuffer($buffer);
            $failed += $chunkFailed;
            $success -= $chunkSuccess;
        }

        ImportRelationResolver::resetCaches();
        $this->linkPendingParents($batch, $pendingParentLinks, $insertedSlugs, $failed, $success);

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'processed_rows' => $processed,
            'success_count' => $success,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'completed_at' => now(),
            'message' => $this->finishedMessage($success, $failed, $skipped),
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
            'slug' => $payload['slug'],
            'name' => json_encode($payload['name'], JSON_UNESCAPED_UNICODE),
            'image' => $payload['image'],
            'is_active' => $payload['is_active'] ? 1 : 0,
            'is_featured' => $payload['is_featured'] ? 1 : 0,
            'sort_order' => $payload['sort_order'] ?? 0,
            'parent_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param  list<array{child_slug: string, parent_reference: ?string, parent_id: ?int, row_number: int, row: array<string, mixed>}>  $pendingParentLinks
     */
    private function linkPendingParents(
        DataTransferBatch $batch,
        array $pendingParentLinks,
        array $insertedSlugs,
        int &$failed,
        int &$success,
    ): void {
        foreach ($pendingParentLinks as $link) {
            if (! isset($insertedSlugs[$link['child_slug']])) {
                continue;
            }

            $parentId = $this->resolveParentIdForLink($link);

            if ($parentId === null) {
                if (ImportRelationResolver::shouldSkipMissingRelations()) {
                    continue;
                }

                $failed++;
                $success = max(0, $success - 1);
                $this->logMissingParentFailure($batch, $link);

                continue;
            }

            Category::query()
                ->where('slug', $link['child_slug'])
                ->update([
                    'parent_id' => $parentId,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * @param  array{child_slug: string, parent_reference: ?string, parent_id: ?int, row_number: int, row: array<string, mixed>}  $link
     */
    private function logMissingParentFailure(DataTransferBatch $batch, array $link): void
    {
        $reference = (string) ($link['parent_reference'] ?? $link['parent_id'] ?? '');

        $validator = Validator::make(
            ['parent_id' => null],
            ['parent_id' => 'required'],
            ['parent_id.required' => __('messages.Parent category could not be resolved: :reference', ['reference' => $reference])],
        );

        $this->logValidationFailure($batch, $link['row_number'], $link['row'], $validator);
    }

    /**
     * @param  array{child_slug: string, parent_reference: ?string, parent_id: ?int, row_number: int, row: array<string, mixed>}  $link
     */
    private function resolveParentIdForLink(array $link): ?int
    {
        if ($link['parent_id'] !== null) {
            return ImportRelationResolver::resolveCategoryParentId((string) $link['parent_id'], $link['child_slug']);
        }

        if ($link['parent_reference'] === null) {
            return null;
        }

        return $this->mapper->resolveParentIdFromReference(
            $link['parent_reference'],
            $link['child_slug'],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $buffer
     * @return array{0: int, 1: int}
     */
    private function flushBuffer(array $buffer): array
    {
        try {
            DB::transaction(function () use ($buffer) {
                Category::query()->insert($buffer);
            });

            return [0, 0];
        } catch (\Throwable $exception) {
            Log::error('Category import chunk failed', [
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
