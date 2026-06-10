<?php

namespace App\V1\Services;

use App\Jobs\DataTransfer\ExportCategoriesJob;
use App\Jobs\DataTransfer\ExportProductsJob;
use App\Jobs\DataTransfer\ExportVariantOptionsJob;
use App\Jobs\DataTransfer\ExportVariantsJob;
use App\Jobs\DataTransfer\ImportCategoriesJob;
use App\Jobs\DataTransfer\ImportProductsJob;
use App\Jobs\DataTransfer\ImportVariantOptionsJob;
use App\Jobs\DataTransfer\ImportVariantsJob;
use App\Models\DataTransferBatch;
use App\V1\DataTransfer\DataTransferConfig;
use App\V1\DataTransfer\Exports\CategoriesExport;
use App\V1\DataTransfer\Exports\CategoriesTemplateExport;
use App\V1\DataTransfer\Exports\ProductsExport;
use App\V1\DataTransfer\Exports\ProductsTemplateExport;
use App\V1\DataTransfer\Exports\VariantOptionsExport;
use App\V1\DataTransfer\Exports\VariantOptionsTemplateExport;
use App\V1\DataTransfer\Exports\VariantsExport;
use App\V1\DataTransfer\Exports\VariantsTemplateExport;
use App\V1\DataTransfer\Support\VariantSpreadsheetColumns;
use App\V1\Repositories\CategoryRepository;
use App\V1\Repositories\ProductRepository;
use App\V1\Repositories\VariantOptionRepository;
use App\V1\Repositories\VariantRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataTransferService
{
    public function __construct(
        protected ProductRepository $products,
        protected CategoryRepository $categories,
        protected VariantRepository $variants,
        protected VariantOptionRepository $variantOptions,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function startProductExport(array $filters = []): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_PRODUCTS, DataTransferBatch::DIRECTION_EXPORT);

        $this->dispatchExportJob(ExportProductsJob::class, $batch->id, $filters);

        return $batch->fresh();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function startCategoryExport(array $filters = []): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_CATEGORIES, DataTransferBatch::DIRECTION_EXPORT);

        $this->dispatchExportJob(ExportCategoriesJob::class, $batch->id, $filters);

        return $batch->fresh();
    }

    public function startProductImport(UploadedFile $file): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_PRODUCTS, DataTransferBatch::DIRECTION_IMPORT);
        $path = $this->storeUpload($file, $batch);

        $batch->update(['file_path' => $path]);

        $this->dispatchImportJob(ImportProductsJob::class, $batch->id);

        return $batch->fresh();
    }

    public function startCategoryImport(UploadedFile $file): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_CATEGORIES, DataTransferBatch::DIRECTION_IMPORT);
        $path = $this->storeUpload($file, $batch);

        $batch->update(['file_path' => $path]);

        $this->dispatchImportJob(ImportCategoriesJob::class, $batch->id);

        return $batch->fresh();
    }

    public function downloadCategoriesTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new CategoriesTemplateExport,
            'categories-import-template.xlsx',
        );
    }

    public function downloadProductsTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new ProductsTemplateExport,
            'products-import-template.xlsx',
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function startVariantExport(array $filters = []): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_VARIANTS, DataTransferBatch::DIRECTION_EXPORT);

        $this->dispatchExportJob(ExportVariantsJob::class, $batch->id, $filters);

        return $batch->fresh();
    }

    public function startVariantImport(UploadedFile $file): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_VARIANTS, DataTransferBatch::DIRECTION_IMPORT);
        $path = $this->storeUpload($file, $batch);

        $batch->update(['file_path' => $path]);

        $this->dispatchImportJob(ImportVariantsJob::class, $batch->id);

        return $batch->fresh();
    }

    public function startVariantOptionExport(): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_VARIANT_OPTIONS, DataTransferBatch::DIRECTION_EXPORT);

        $this->dispatchExportJob(ExportVariantOptionsJob::class, $batch->id);

        return $batch->fresh();
    }

    public function startVariantOptionImport(UploadedFile $file): DataTransferBatch
    {
        $batch = $this->createBatch(DataTransferBatch::ENTITY_VARIANT_OPTIONS, DataTransferBatch::DIRECTION_IMPORT);
        $path = $this->storeUpload($file, $batch);

        $batch->update(['file_path' => $path]);

        $this->dispatchImportJob(ImportVariantOptionsJob::class, $batch->id);

        return $batch->fresh();
    }

    public function downloadVariantsTemplate(): BinaryFileResponse
    {
        return Excel::download(new VariantsTemplateExport, 'variants-import-template.xlsx');
    }

    public function downloadVariantOptionsTemplate(): BinaryFileResponse
    {
        return Excel::download(new VariantOptionsTemplateExport, 'variant-options-import-template.xlsx');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function runProductExport(DataTransferBatch $batch, array $filters = []): void
    {
        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $products = $this->products->getPaginatedProducts(
            perPage: 100000,
            filters: $filters,
        )->getCollection();

        $relativePath = DataTransferConfig::exportRelativePath($batch->entity);

        Excel::store(
            new ProductsExport($products),
            $relativePath,
            DataTransferConfig::DISK,
        );

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'file_path' => $relativePath,
            'total_rows' => $products->count(),
            'processed_rows' => $products->count(),
            'success_count' => $products->count(),
            'completed_at' => now(),
            'message' => __('messages.Export completed successfully.'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function runCategoryExport(DataTransferBatch $batch, array $filters = []): void
    {
        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $categories = $this->categories->getPaginatedCategories(
            perPage: 100000,
            filters: $filters,
        )->getCollection();

        $relativePath = DataTransferConfig::exportRelativePath($batch->entity);

        Excel::store(
            new CategoriesExport($categories),
            $relativePath,
            DataTransferConfig::DISK,
        );

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'file_path' => $relativePath,
            'total_rows' => $categories->count(),
            'processed_rows' => $categories->count(),
            'success_count' => $categories->count(),
            'completed_at' => now(),
            'message' => __('messages.Export completed successfully.'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function runVariantExport(DataTransferBatch $batch, array $filters = []): void
    {
        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $variants = $this->variants->getPaginatedVariants(100000, $filters)->getCollection();
        $rows = VariantSpreadsheetColumns::flattenVariants($variants);
        $relativePath = DataTransferConfig::exportRelativePath($batch->entity);

        Excel::store(new VariantsExport($rows), $relativePath, DataTransferConfig::DISK);

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'file_path' => $relativePath,
            'total_rows' => $rows->count(),
            'processed_rows' => $rows->count(),
            'success_count' => $rows->count(),
            'completed_at' => now(),
            'message' => __('messages.Export completed successfully.'),
        ]);
    }

    public function runVariantOptionExport(DataTransferBatch $batch): void
    {
        $batch->update([
            'status' => DataTransferBatch::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $items = $this->variantOptions->getAllVariantOptions();
        $relativePath = DataTransferConfig::exportRelativePath($batch->entity);

        Excel::store(new VariantOptionsExport($items), $relativePath, DataTransferConfig::DISK);

        $batch->update([
            'status' => DataTransferBatch::STATUS_COMPLETED,
            'file_path' => $relativePath,
            'total_rows' => $items->count(),
            'processed_rows' => $items->count(),
            'success_count' => $items->count(),
            'completed_at' => now(),
            'message' => __('messages.Export completed successfully.'),
        ]);
    }

    /**
     * @return Collection<int, DataTransferBatch>
     */
    public function recentBatches(string $entity, int $limit = 8): Collection
    {
        return DataTransferBatch::query()
            ->where('entity', $entity)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{import: Collection<int, DataTransferBatch>, export: Collection<int, DataTransferBatch>}
     */
    public function recentBatchesGrouped(string $entity, int $limit = 10): array
    {
        return [
            'import' => DataTransferBatch::query()
                ->where('entity', $entity)
                ->where('direction', DataTransferBatch::DIRECTION_IMPORT)
                ->latest()
                ->limit($limit)
                ->get(),
            'export' => DataTransferBatch::query()
                ->where('entity', $entity)
                ->where('direction', DataTransferBatch::DIRECTION_EXPORT)
                ->latest()
                ->limit($limit)
                ->get(),
        ];
    }

    public function runsImportSynchronously(): bool
    {
        return (bool) config('data-transfer.run_import_sync', true);
    }

    public function downloadExport(DataTransferBatch $batch): BinaryFileResponse|StreamedResponse
    {
        return Storage::disk(DataTransferConfig::DISK)->download(
            $batch->file_path,
            basename((string) $batch->file_path),
        );
    }

    /**
     * @param  class-string  $jobClass
     */
    private function dispatchImportJob(string $jobClass, mixed ...$arguments): void
    {
        if ($this->runsImportSynchronously()) {
            $jobClass::dispatchSync(...$arguments);

            return;
        }

        $jobClass::dispatch(...$arguments);
    }

    /**
     * @param  class-string  $jobClass
     */
    private function dispatchExportJob(string $jobClass, mixed ...$arguments): void
    {
        $jobClass::dispatchSync(...$arguments);
    }

    private function createBatch(string $entity, string $direction): DataTransferBatch
    {
        $attributes = [
            'user_id' => Auth::id(),
            'entity' => $entity,
            'direction' => $direction,
            'status' => DataTransferBatch::STATUS_PENDING,
        ];

        if (! $this->runsImportSynchronously() && $direction === DataTransferBatch::DIRECTION_IMPORT) {
            $attributes['message'] = __('messages.Import job queued.');
        }

        return DataTransferBatch::query()->create($attributes);
    }

    public function resolveImportAbsolutePath(DataTransferBatch $batch): string
    {
        $relativePath = is_string($batch->file_path) ? trim($batch->file_path) : '';

        if ($relativePath === '') {
            throw new \RuntimeException('Import file path is missing for batch #'.$batch->id.'.');
        }

        $absolutePath = Storage::disk(DataTransferConfig::DISK)->path($relativePath);

        if (! is_file($absolutePath)) {
            throw new \RuntimeException('Import file not found: '.$relativePath);
        }

        return $absolutePath;
    }

    private function storeUpload(UploadedFile $file, DataTransferBatch $batch): string
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('The uploaded file is invalid or did not upload correctly.');
        }

        $sourcePath = $file->getPathname();
        if ($sourcePath === '' || ! is_readable($sourcePath)) {
            throw new \RuntimeException('The uploaded file could not be read from disk.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $filename = $batch->entity.'-'.$batch->id.'.'.$extension;
        $relativePath = DataTransferConfig::IMPORT_DIR.'/'.$filename;

        $contents = file_get_contents($sourcePath);
        if ($contents === false) {
            throw new \RuntimeException('The uploaded file could not be read from disk.');
        }

        $disk = Storage::disk(DataTransferConfig::DISK);
        $disk->makeDirectory(DataTransferConfig::IMPORT_DIR);

        if (! $disk->put($relativePath, $contents)) {
            throw new \RuntimeException('Failed to store the import file.');
        }

        return $relativePath;
    }
}
