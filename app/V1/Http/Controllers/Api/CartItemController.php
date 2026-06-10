<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\V1\Http\Requests\Api\ApplyCartCouponRequest;
use App\V1\Http\Requests\Rules\CartItemValidation;
use App\V1\Http\Resources\Api\CartItemResource;
use App\V1\Http\Resources\Api\CouponResource;
use App\V1\Services\CartItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartItemController extends Controller
{
    public function __construct(
        protected CartItemService $cartItemService,
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $appliedCoupon = $this->cartItemService->getAppliedCoupon($userId);
        $subtotal = $this->cartItemService->calculateCartSubtotal($userId);

        $checkoutPreview = $this->cartItemService->getCheckoutPreview($userId);

        return CartItemResource::collection(
            $this->cartItemService->getUserCartItems($userId)
        )->additional([
            'meta' => array_merge([
                'total_quantity' => $this->cartItemService->getCartTotal($userId),
                'subtotal' => $subtotal,
                'total_price' => $subtotal,
                'total_discount' => $this->cartItemService->getCartTotalDiscount($userId),
                'coupon' => $appliedCoupon ? new CouponResource($appliedCoupon) : null,
            ], $checkoutPreview),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $this->assertVariantForProduct($request, $product);
        $validated = $request->validate(CartItemValidation::add());

        $cartItem = $this->cartItemService->addOrIncrement(
            $request->user()->id,
            $product,
            $validated['variant_id'] ?? null,
            (int) ($validated['quantity'] ?? 1),
            isset($validated['product_unit_id']) ? (int) $validated['product_unit_id'] : null,
        );

        return $this->cartItemResponse($cartItem);
    }

    public function update(Request $request, Product $product)
    {
        return $this->performQuantityUpdate($request, $product);
    }

    public function updateByCartItem(Request $request, CartItem $cartItem)
    {
        if ((int) $cartItem->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $this->mergeQuantityInput($request);
        $validated = $request->validate(CartItemValidation::updateByCartItem());

        $cartItem = $this->cartItemService->updateQuantityById(
            $request->user()->id,
            $cartItem->id,
            (int) $validated['quantity'],
        );

        return $this->cartItemResponse($cartItem);
    }

    protected function performQuantityUpdate(Request $request, Product $product): JsonResponse
    {
        $this->assertVariantForProduct($request, $product);
        $this->mergeQuantityInput($request);
        $validated = $request->validate(CartItemValidation::updateQuantity());

        $cartItem = $this->cartItemService->updateQuantity(
            $request->user()->id,
            $product,
            (int) $validated['quantity'],
            $validated['variant_id'] ?? null,
            isset($validated['product_unit_id']) ? (int) $validated['product_unit_id'] : null,
        );

        return $this->cartItemResponse($cartItem);
    }

    protected function cartItemResponse(CartItem $cartItem): JsonResponse
    {
        return (new CartItemResource($cartItem->load(['product.categories', 'product.productUnits.unit', 'product', 'variant', 'productUnit.unit'])))
            ->response()
            ->setStatusCode(200);
    }

    protected function mergeQuantityInput(Request $request): void
    {
        $quantity = $this->resolveUpdateQuantity($request);

        if ($quantity !== null) {
            $request->merge(['quantity' => $quantity]);
        }
    }

    protected function resolveUpdateQuantity(Request $request): ?int
    {
        $content = $request->getContent();
        if ($content !== '') {
            $decoded = json_decode($content, true);
            if (is_array($decoded) && array_key_exists('quantity', $decoded) && $decoded['quantity'] !== null && $decoded['quantity'] !== '') {
                return (int) $decoded['quantity'];
            }
        }

        if ($request->isJson()) {
            $quantity = $request->json('quantity');
            if ($quantity !== null && $quantity !== '') {
                return (int) $quantity;
            }
        }

        if ($request->request->has('quantity')) {
            return (int) $request->request->get('quantity');
        }

        if ($request->query->has('quantity')) {
            return (int) $request->query->get('quantity');
        }

        return null;
    }

    public function destroy(Request $request, Product $product)
    {
        $this->assertVariantForProduct($request, $product);
        $validated = $request->validate(CartItemValidation::remove());

        $this->cartItemService->removeItem(
            $request->user()->id,
            $product,
            $validated['variant_id'] ?? null,
            isset($validated['product_unit_id']) ? (int) $validated['product_unit_id'] : null,
        );

        return response()->json(['message' => 'Product removed from cart.']);
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartItemService->clearCart($request->user()->id);

        return response()->json([
            'message' => 'Cart cleared successfully.',
        ]);
    }

    public function checkoutPreview(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->cartItemService->getCheckoutPreview($request->user()->id),
        ]);
    }

    public function applyCoupon(ApplyCartCouponRequest $request): JsonResponse
    {
        $result = $this->cartItemService->applyCoupon(
            $request->user()->id,
            $request->validated('code'),
        );

        return response()->json([
            'coupon' => new CouponResource($result['coupon']),
            'cart_subtotal' => $result['subtotal'],
            'discount' => $result['discount'],
            'grants_free_delivery' => $result['grants_free_delivery'],
            'total' => max(0, $result['subtotal'] - $result['discount']),
        ]);
    }

    protected function assertVariantForProduct(Request $request, Product $product): void
    {
        if ($product->isVariable()) {
            $request->validate([
                'variant_id' => [
                    'required',
                    'integer',
                    Rule::exists('product_variants', 'id')->where('product_id', $product->id),
                ],
            ]);

            return;
        }

        // Ignore stray variant_id on simple products (common in API clients).
        $request->merge(['variant_id' => null]);
    }
}
