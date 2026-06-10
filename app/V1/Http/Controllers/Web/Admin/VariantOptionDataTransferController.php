<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\DataTransferBatch;
use App\V1\Http\Requests\Web\Admin\ImportSpreadsheetRequest;
use App\V1\Services\DataTransferService;
use App\V1\Services\VariantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VariantOptionDataTransferController extends AdminController
{
    public function __construct(
        protected DataTransferService $dataTransfer,
        protected VariantService $variants,
    ) {}

    public function create(): View
    {
        $batches = $this->dataTransfer->recentBatchesGrouped(DataTransferBatch::ENTITY_VARIANT_OPTIONS);

        return view('v1.admin.variant-options.import', [
            'allVariants' => $this->variants->getAllVariants(),
            'importBatches' => $batches['import'],
            'exportBatches' => $batches['export'],
        ]);
    }

    public function export(): RedirectResponse
    {
        $batch = $this->dataTransfer->startVariantOptionExport();

        $batch = $batch->fresh();

        if ($batch->status === DataTransferBatch::STATUS_COMPLETED && $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.variant-options.import-export.download', $batch)
                ->with('success', __('messages.Export completed successfully.'));
        }

        return $this->redirectWithSuccess('v1.admin.variant-options.index', __('messages.Variant option export started. It will run in the background.'));
    }

    public function store(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $batch = $this->dataTransfer->startVariantOptionImport($request->file('file'));

        return redirect()
            ->route('v1.admin.variant-options.import-export.show', $batch)
            ->with('success', __('messages.Variant option import finished.'));
    }

    public function template(): BinaryFileResponse
    {
        return $this->dataTransfer->downloadVariantOptionsTemplate();
    }

    public function show(DataTransferBatch $batch): View
    {
        abort_unless($batch->entity === DataTransferBatch::ENTITY_VARIANT_OPTIONS, 404);

        $batch->load(['rowLogs' => fn ($query) => $query->orderBy('row_number')->limit(500)]);

        return view('v1.admin.data-transfer.show', [
            'batch' => $batch,
            'backRoute' => 'v1.admin.variant-options.import',
            'downloadRoute' => 'v1.admin.variant-options.import-export.download',
            'title' => __('messages.Variant option import/export'),
        ]);
    }

    public function download(DataTransferBatch $batch): StreamedResponse|RedirectResponse
    {
        abort_unless(
            $batch->entity === DataTransferBatch::ENTITY_VARIANT_OPTIONS
            && $batch->direction === DataTransferBatch::DIRECTION_EXPORT,
            404,
        );

        if (! $batch->hasDownloadableFile()) {
            return redirect()
                ->route('v1.admin.variant-options.import')
                ->with('error', __('messages.Export file is no longer available.'));
        }

        return $this->dataTransfer->downloadExport($batch);
    }
}
