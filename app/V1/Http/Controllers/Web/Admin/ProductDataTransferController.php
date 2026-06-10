<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\DataTransferBatch;
use App\V1\Http\Requests\Web\Admin\ImportSpreadsheetRequest;
use App\V1\Services\BrandService;
use App\V1\Services\CategoryService;
use App\V1\Services\DataTransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductDataTransferController extends AdminController
{
    public function __construct(
        protected DataTransferService $dataTransfer,
        protected BrandService $brands,
        protected CategoryService $categories,
    ) {}

    public function create(): View
    {
        return view('v1.admin.products.import', [
            'brands' => $this->brands->getAllBrands(),
            'allCategories' => $this->categories->getAllCategories(),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startProductExport(
            $this->listFilters($request, [
                'search', 'status', 'type', 'category_id', 'featured', 'sort',
            ]),
        );

        $batch = $batch->fresh();

        if ($batch->status === DataTransferBatch::STATUS_COMPLETED && $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.products.import-export.download', $batch)
                ->with('success', __('messages.Export completed successfully.'));
        }

        return $this->redirectWithSuccess(
            'v1.admin.products.index',
            __('messages.Product export started. It will run in the background.'),
        );
    }

    public function store(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startProductImport($request->file('file'));

        $message = $this->dataTransfer->runsImportSynchronously()
            ? __('messages.Product import finished.')
            : __('messages.Product import started. It will run in the background.');

        return redirect()
            ->route('v1.admin.products.import-export.show', $batch)
            ->with('success', $message);
    }

    public function template(): BinaryFileResponse
    {
        return $this->dataTransfer->downloadProductsTemplate();
    }

    public function show(DataTransferBatch $batch): View
    {
        abort_unless($batch->entity === DataTransferBatch::ENTITY_PRODUCTS, 404);

        $batch->load(['rowLogs' => fn ($query) => $query->orderBy('row_number')->limit(500)]);

        return view('v1.admin.data-transfer.show', [
            'batch' => $batch,
            'backRoute' => 'v1.admin.products.import',
            'downloadRoute' => 'v1.admin.products.import-export.download',
            'title' => __('messages.Product import/export'),
        ]);
    }

    public function download(DataTransferBatch $batch): StreamedResponse|RedirectResponse
    {
        abort_unless(
            $batch->entity === DataTransferBatch::ENTITY_PRODUCTS
            && $batch->direction === DataTransferBatch::DIRECTION_EXPORT,
            404,
        );

        if (! $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.products.import')
                ->with('error', __('messages.Export file is no longer available.'));
        }

        return $this->dataTransfer->downloadExport($batch);
    }
}
