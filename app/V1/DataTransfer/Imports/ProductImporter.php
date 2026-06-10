<?php

namespace App\V1\DataTransfer\Imports;

use App\Models\DataTransferBatch;
use App\Models\Product;
use App\V1\DataTransfer\Concerns\LogsImportFailures;
use App\V1\DataTransfer\DataTransferConfig;
use App\V1\DataTransfer\Support\ImportDuplicateGuard;
use App\V1\DataTransfer\Support\ImportRelationResolver;
use App\V1\DataTransfer\Support\ProductSpreadsheetColumns;
use App\V1\Http\Requests\Rules\ProductValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductImporter
{
    use LogsImportFailures;

    public function __construct(
        protected SpreadsheetReader $reader,
        protected ProductRowMapper $mapper,
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
        $categoryMap = [];
        $duplicateGuard = new ImportDuplicateGuard;
        $processed = 0;
        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $rowNumber = (int) ($row['_spreadsheet_row'] ?? ($processed + 2));
            unset($row['_spreadsheet_row']);

            try {
                $canonicalRow = ProductSpreadsheetColumns::canonicalizeRow($row);
                $mapped = $this->mapper->toPayload($canonicalRow);
                $product = ProductValidation::normalizeOptionalFields($mapped['product']);
                $categoryIds = $mapped['category_ids'];
                $syncCategories = $mapped['sync_categories'];
                $categoriesRaw = ProductSpreadsheetColumns::categoriesRawValue($canonicalRow);

                if ($duplicateGuard->shouldSkipProduct($product)) {
                    $skipped++;
                } else {
                    $validator = Validator::make(
                        array_merge($product, ['category_ids' => $categoryIds]),
                        ProductValidation::importRow($product, $categoryIds),
                    );

                    if ($validator->fails()) {
                        $failed++;
                        $this->logValidationFailure($batch, $rowNumber, $row, $validator);
                    } elseif ($syncCategories && $categoriesRaw !== '' && $categoryIds === []) {
                        $failed++;
                        $this->logValidationFailure($batch, $rowNumber, $row, Validator::make(
                            ['categories' => $categoriesRaw],
                            ['categories' => ['required']],
                            ['categories.required' => __('messages.Import could not resolve categories: :value', ['value' => $categoriesRaw])],
                        ));
                    } else {
                        $sku = $product['sku'];
                        $buffer[] = $this->prepareRecord($product);
                        $categoryMap[$sku] = [
                            'ids' => $categoryIds,
                            'sync' => $syncCategories,
                        ];
                        $success++;
                    }
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->logRowException($batch, $rowNumber, $row, $exception);
            }

            $processed++;

            if (count($buffer) >= $this->chunkSize()) {
                [$chunkFailed, $chunkSuccess] = $this->flushBuffer($buffer, $categoryMap);
                $failed += $chunkFailed;
                $success -= $chunkSuccess;
                $buffer = [];
                $categoryMap = [];
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
            [$chunkFailed, $chunkSuccess] = $this->flushBuffer($buffer, $categoryMap);
            $failed += $chunkFailed;
            $success -= $chunkSuccess;
        }

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
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function prepareRecord(array $product): array
    {
        $now = now();

        return [
            'type' => $product['type'],
            'name' => json_encode($product['name'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode($product['description'], JSON_UNESCAPED_UNICODE),
            'thumbnail' => $product['thumbnail'],
            'sku' => $product['sku'],
            'slug' => $product['slug'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'is_in_stock' => ($product['is_in_stock'] ?? true) ? 1 : 0,
            'sort_order' => $product['sort_order'] ?? null,
            'discount' => $product['discount'],
            'discount_type' => $product['discount_type'],
            'is_active' => $product['is_active'] ? 1 : 0,
            'is_featured' => $product['is_featured'] ? 1 : 0,
            'is_new' => $product['is_new'] ? 1 : 0,
            'is_approved' => $product['is_approved'] ? 1 : 0,
            'is_bookable' => $product['is_bookable'] ? 1 : 0,
            'brand_id' => $product['brand_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $buffer
     * @param  array<string, list<int>>  $categoryMap
     * @return array{0: int, 1: int}
     */
    private function flushBuffer(array $buffer, array $categoryMap): array
    {
        try {
            DB::transaction(function () use ($buffer, $categoryMap) {
                Product::query()->insert($buffer);

                $skus = array_column($buffer, 'sku');

                if ($skus !== []) {
                    $products = Product::query()
                        ->select(['id', 'sku'])
                        ->whereIn('sku', $skus, 'and', false)
                        ->get();

                    foreach ($products as $product) {
                        $categoryEntry = $categoryMap[$product->sku] ?? null;

                        if (! is_array($categoryEntry) || ! ($categoryEntry['sync'] ?? false)) {
                            continue;
                        }

                        $product->categories()->sync($categoryEntry['ids'] ?? []);
                    }

                    $productsWithRelations = Product::query()
                        ->with(['brand', 'categories'])
                        ->whereIn('sku', $skus, 'and', false)
                        ->get();

                    foreach ($productsWithRelations as $product) {
                        $product->syncBrandSnapshot();
                        $product->syncCategorySnapshot();
                        $product->saveQuietly();
                    }
                }
            });

            return [0, 0];
        } catch (\Throwable $exception) {
            Log::error('Product import chunk failed', [
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
