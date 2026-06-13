<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderRating;
use App\V1\Http\Requests\Api\StoreOrderRatingRequest;
use App\V1\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderRatingController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function store(StoreOrderRatingRequest $request, int $order): JsonResponse
    {
        $order = $this->orderService->getOrderByIdForUser($order, $request->user()->id);

        if ($order === null) {
            return $this->errorResponse(__('messages.Order not found in your account.'), 404);
        }

        if ($order->status !== 'delivered') {
            return $this->errorResponse(__('messages.You can only rate an order after it is delivered.'), 422);
        }

        if ($order->orderRating()->exists()) {
            return $this->errorResponse(__('messages.You have already rated this order.'), 422);
        }

        $rating = OrderRating::query()->create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.Order rated successfully.'),
            'data' => [
                'rating' => $rating,
            ],
        ], 201);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
