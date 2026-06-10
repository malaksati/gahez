<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\DataTransferBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTransferBatchController extends AdminController
{
    public function status(Request $request, DataTransferBatch $batch): JsonResponse
    {
        abort_unless($request->user()?->can($this->permissionForEntity($batch->entity)), 403);

        $batch->refresh();

        $isStalePending = $batch->status === DataTransferBatch::STATUS_PENDING
            && $batch->created_at !== null
            && $batch->created_at->diffInSeconds(now()) >= 15;

        return response()->json([
            'status' => $batch->status,
            'direction' => $batch->direction,
            'processed_rows' => $batch->processed_rows,
            'total_rows' => $batch->total_rows,
            'success_count' => $batch->success_count,
            'failed_count' => $batch->failed_count,
            'skipped_count' => $batch->skipped_count ?? 0,
            'message' => $batch->message,
            'is_finished' => $batch->isFinished(),
            'has_downloadable_file' => $batch->hasDownloadableFile(),
            'is_stale_pending' => $isStalePending,
        ]);
    }

    private function permissionForEntity(string $entity): string
    {
        return match ($entity) {
            DataTransferBatch::ENTITY_PRODUCTS => 'manage products',
            DataTransferBatch::ENTITY_CATEGORIES => 'manage categories',
            DataTransferBatch::ENTITY_VARIANTS,
            DataTransferBatch::ENTITY_VARIANT_OPTIONS => 'manage variants',
            default => abort(404),
        };
    }
}
