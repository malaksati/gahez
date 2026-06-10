<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\DataTransferBatch;
use App\V1\Http\Requests\Web\Admin\ImportSpreadsheetRequest;
use App\V1\Services\CategoryService;
use App\V1\Services\DataTransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryDataTransferController extends AdminController
{
    public function __construct(
        protected DataTransferService $dataTransfer,
        protected CategoryService $categories,
    ) {}

    public function create(): View
    {
        return view('v1.admin.categories.import', [
            'allCategories' => $this->categories->getAllCategories(),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startCategoryExport(
            $this->listFilters($request, [
                'search', 'status', 'featured', 'parent_id', 'sort',
            ]),
        );

        $batch = $batch->fresh();

        if ($batch->status === DataTransferBatch::STATUS_COMPLETED && $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.categories.import-export.download', $batch)
                ->with('success', __('messages.Export completed successfully.'));
        }

        return $this->redirectWithSuccess(
            'v1.admin.categories.index',
            __('messages.Category export started. It will run in the background.'),
        );
    }

    public function store(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startCategoryImport($request->file('file'));

        return redirect()
            ->route('v1.admin.categories.import-export.show', $batch)
            ->with('success', __('messages.Category import finished.'));
    }

    public function template(): BinaryFileResponse
    {
        return $this->dataTransfer->downloadCategoriesTemplate();
    }

    public function show(DataTransferBatch $batch): View
    {
        abort_unless($batch->entity === DataTransferBatch::ENTITY_CATEGORIES, 404);

        $batch->load(['rowLogs' => fn ($query) => $query->orderBy('row_number')->limit(500)]);

        return view('v1.admin.data-transfer.show', [
            'batch' => $batch,
            'backRoute' => 'v1.admin.categories.import',
            'downloadRoute' => 'v1.admin.categories.import-export.download',
            'title' => __('messages.Category import/export'),
        ]);
    }

    public function download(DataTransferBatch $batch): StreamedResponse|RedirectResponse
    {
        abort_unless(
            $batch->entity === DataTransferBatch::ENTITY_CATEGORIES
            && $batch->direction === DataTransferBatch::DIRECTION_EXPORT,
            404,
        );

        if (! $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.categories.import')
                ->with('error', __('messages.Export file is no longer available.'));
        }

        return $this->dataTransfer->downloadExport($batch);
    }
}
