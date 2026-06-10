<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\V1\Http\Resources\Api\ProductResource;
use App\V1\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlists,
    ) {}

    public function index(Request $request)
    {
        $items = $this->wishlists->listForUser($request->user()->id);

        return ProductResource::collection(
            $items->map(fn ($item) => $item->product)->filter()
        );
    }

    public function toggle(Request $request, Product $product): JsonResponse
    {
        $result = $this->wishlists->toggle($request->user(), $product);

        return response()->json($result);
    }
}
