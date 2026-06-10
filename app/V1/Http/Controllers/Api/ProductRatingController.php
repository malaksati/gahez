<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\V1\Http\Resources\Api\ProductRatingResource;
use App\V1\Services\ProductRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductRatingController extends Controller
{
    public function __construct(
        protected ProductRatingService $ratings,
    ) {}

    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = $this->ratings->rateProduct(
            $request->user()->id,
            $product->id,
            (int) $validated['rating'],
            $validated['comment'] ?? null,
        );

        return response()->json([
            'message' => 'Product rated successfully.',
            'data' => new ProductRatingResource($rating->load('user')),
        ]);
    }
}
