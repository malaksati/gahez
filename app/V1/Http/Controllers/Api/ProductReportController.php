<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\V1\Services\ProductReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReportController extends Controller
{
    public function __construct(
        protected ProductReportService $reports,
    ) {}

    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->reports->reportProduct(
            $request->user()->id,
            $product->id,
            $validated['reason'] ?? null,
            $validated['description'] ?? null,
        );

        return response()->json([
            'message' => 'Product reported successfully.',
        ]);
    }
}
