<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRating;
use App\V1\Http\Requests\Api\StoreOrderRatingRequest;
use Illuminate\Http\JsonResponse;

class OrderRatingController extends Controller
{
    public function store(StoreOrderRatingRequest $request, Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($order->status !== 'delivered') {
            return response()->json([
                'message' => 'You can only rate delivered orders.',
            ], 400);
        }

        if ($order->rating) {
            return response()->json([
                'message' => 'You have already rated this order.',
            ], 400);
        }

        $rating = OrderRating::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
        ]);

        return response()->json([
            'message' => 'Order rated successfully.',
            'data' => [
                'rating' => $rating,
            ],
        ], 201);
    }
}
