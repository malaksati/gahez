<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\V1\Http\Resources\Api\OrderRefundRequestResource;
use App\V1\Services\OrderRefundRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderRefundRequestController extends Controller
{
    public function __construct(
        protected OrderRefundRequestService $refundRequests,
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        return OrderRefundRequestResource::collection(
            $this->refundRequests->getPaginatedForUser($request->user()->id, $perPage)
        );
    }

    public function store(Request $request, int $order): JsonResponse
    {
        $orderModel = Order::query()
            ->where('id', $order)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $refundRequest = $this->refundRequests->createForUser(
            $orderModel,
            $request->user()->id,
            $validated['reason'] ?? null,
            $validated['details'] ?? null,
        );

        return (new OrderRefundRequestResource($refundRequest->load('order')))
            ->response()
            ->setStatusCode(201);
    }
}
