<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\DataTransferBatch;
use App\V1\Http\Requests\Web\Admin\ImportSpreadsheetRequest;
use App\V1\Services\DataTransferService;
use App\V1\Services\VariantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VariantDataTransferController extends AdminController
{
    public function __construct(
        protected DataTransferService $dataTransfer,
        protected VariantService $variants,
    ) {}

    public function create(): View
    {
        return view('v1.admin.variants.import', [
            'allVariants' => $this->variants->getAllVariants(),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startVariantExport(
            $this->listFilters($request, ['search', 'status', 'required']),
        );

        $batch = $batch->fresh();

        if ($batch->status === DataTransferBatch::STATUS_COMPLETED && $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.variants.import-export.download', $batch)
                ->with('success', __('messages.Export completed successfully.'));
        }

        return $this->redirectWithSuccess('v1.admin.variants.index', __('messages.Variant export started. It will run in the background.'));
    }

    public function store(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startVariantImport($request->file('file'));

        return redirect()
            ->route('v1.admin.variants.import-export.show', $batch)
            ->with('success', __('messages.Variant import finished.'));
    }

    public function template(): BinaryFileResponse
    {
        return $this->dataTransfer->downloadVariantsTemplate();
    }

    public function show(DataTransferBatch $batch): View
    {
        abort_unless($batch->entity === DataTransferBatch::ENTITY_VARIANTS, 404);

        $batch->load(['rowLogs' => fn ($query) => $query->orderBy('row_number')->limit(500)]);

        return view('v1.admin.data-transfer.show', [
            'batch' => $batch,
            'backRoute' => 'v1.admin.variants.import',
            'downloadRoute' => 'v1.admin.variants.import-export.download',
            'title' => __('messages.Variant import/export'),
        ]);
    }

    public function download(DataTransferBatch $batch): StreamedResponse|RedirectResponse
    {
        abort_unless(
            $batch->entity === DataTransferBatch::ENTITY_VARIANTS
            && $batch->direction === DataTransferBatch::DIRECTION_EXPORT,
            404,
        );

        if (! $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.variants.import')
                ->with('error', __('messages.Export file is no longer available.'));
        }

        return $this->dataTransfer->downloadExport($batch);
    }
}
