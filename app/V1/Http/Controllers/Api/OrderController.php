<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\V1\Http\Requests\Api\PayOrderRequest;
use App\V1\Http\Requests\Api\StoreOrderRequest;
use App\V1\Http\Resources\Api\OrderResource;
use App\V1\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrderFromCart(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function pay(PayOrderRequest $request, int $order): JsonResponse
    {
        $paidOrder = $this->orderService->payOrderForUser(
            $order,
            $request->user()->id,
            $request->validated('payment_method'),
        );

        abort_if($paidOrder === null, 404);

        return response()->json([
            'message' => 'Order paid successfully.',
            'data' => new OrderResource($paidOrder),
        ]);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        if ($request->boolean('paginate') || $request->filled('per_page')) {
            return OrderResource::collection(
                $this->orderService->getPaginatedOrdersForUser(
                    $request->user()->id,
                    $perPage,
                    $this->orderFilters($request)
                )
            );
        }

        return OrderResource::collection(
            $this->orderService->getOrdersByUser($request->user()->id)
        );
    }

    public function show(Request $request, int $id)
    {
        $order = $this->orderService->getOrderByIdForUser($id, $request->user()->id);

        abort_if($order === null, 404);

        return new OrderResource($order);
    }

    public function cancel(Request $request, int $order): JsonResponse
    {
        $cancelled = $this->orderService->cancelOrderForUser($order, $request->user()->id);

        abort_if($cancelled === null, 404);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($cancelled),
        ]);
    }

    public function reorder(Request $request, int $order): JsonResponse
    {
        $result = $this->orderService->reorder($order, $request->user()->id);

        return response()->json([
            'message' => 'Items added to cart.',
            'data' => $result,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderFilters(Request $request): array
    {
        return $request->only([
            'status',
            'payment_status',
            'payment_method',
            'refund_status',
            'from_date',
            'to_date',
            'min_total',
            'max_total',
            'sort',
        ]);
    }
}
