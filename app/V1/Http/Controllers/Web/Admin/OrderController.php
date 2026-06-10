<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\V1\Http\Requests\Web\Admin\StoreOrderRequest;
use App\V1\Http\Requests\Web\Admin\UpdateOrderPaymentStatusRequest;
use App\V1\Http\Requests\Web\Admin\UpdateOrderRequest;
use App\V1\Http\Requests\Web\Admin\UpdateOrderStatusRequest;
use App\V1\Services\AddressService;
use App\V1\Services\CustomerService;
use App\V1\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;

class OrderController extends AdminController
{
    public function __construct(
        protected OrderService $orders,
        protected CustomerService $customers,
        protected AddressService $addresses,
    ) {}

    public function index(Request $request): View|string
    {
        $filters = $request->only([
            'search',
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

        if ($request->has('show_all')) {
            unset($filters['status']);
        } elseif (!isset($filters['status']) || $filters['status'] === '') {
            $filters['status'] = 'pending,processing,ready_for_delivery,shipped';
        }

        $orders = $this->orders->getPaginatedOrders(15, $filters);

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('v1.admin.orders.partials.table', ['orders' => $orders])->render();
        }

        return view('v1.admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $locale = app()->getLocale();

        return view('v1.admin.orders.create', [
            'users' => User::with('addresses')->where('role', 'user')->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::with('variants')->where('is_active', true)->get(),
            'productsData' => Product::with('variants')->where('is_active', true)->get()->map(function (Product $product) use ($locale) {
                $name = $product->getTranslation('name', $locale, false) ?: $product->getTranslation('name', 'en', false);

                return [
                    'id' => $product->id,
                    'name' => $name,
                    'type' => $product->type,
                    'price' => $product->hasDiscount() ? $product->final_price : $product->price,
                    'variants' => $product->variants->map(function ($variant) use ($locale) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->getTranslation('name', $locale, false) ?: $variant->getTranslation('name', 'en', false),
                            'price' => $variant->price,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {
            if ($validated['customer_type'] === 'new') {
                $user = $this->customers->create([
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'] ?? null,
                    'phone' => $validated['customer_phone'] ?? null,
                    'password' => Str::password(12),
                ]);
            } else {
                $user = User::query()->findOrFail($validated['user_id']);
            }

            $address = null;

            if (
                $validated['customer_type'] === 'existing'
                && ! empty($validated['address_id'])
            ) {
                $address = Address::query()
                    ->where('user_id', $user->id)
                    ->where('id', $validated['address_id'])
                    ->firstOrFail();
            } elseif ($validated['customer_type'] === 'new' || ($validated['address_mode'] ?? null) === 'new') {
                $address = $this->addresses->create($user->id, [
                    'name' => $validated['address_name'],
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'phone' => $validated['address_phone'] ?? $user->phone,
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'is_default' => true,
                    'is_active' => true,
                ]);
            }

            $subTotal = 0.0;
            foreach ($validated['items'] as $item) {
                $subTotal += $item['unit_price'] * $item['quantity'];
            }

            $shippingCost = (float) ($validated['shipping_cost'] ?? 0);
            $total = $subTotal + $shippingCost;

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address?->id,
                'status' => $validated['status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'sub_total' => $subTotal,
                'total' => $total,
                'total_shipping' => $shippingCost,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'customer_email' => $user->email,
                'shipping_address_snapshot' => $address ? [
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'address' => $address->address,
                    'city' => $address->city,
                    'state' => $address->state,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ] : null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $variant = ! empty($item['variant_id'])
                    ? \App\Models\ProductVariant::query()->find($item['variant_id'])
                    : null;

                $order->items()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'product_name' => $product->getTranslation('name', 'en', false),
                    'product_name_ar' => $product->getTranslation('name', 'ar', false),
                    'variant_name' => $variant ? $variant->getTranslation('name', 'en', false) : null,
                    'variant_name_ar' => $variant ? $variant->getTranslation('name', 'ar', false) : null,
                    'note' => filled($item['note'] ?? null) ? $item['note'] : null,
                ]);
            }

            $this->orders->logOrderEvent($order, 'created', null, 'Manual order created by admin', auth()->id());

            return $order;
        });

        return redirect()->route('v1.admin.orders.show', $order)
            ->with('success', __('messages.Order created successfully.'));
    }

    public function show(Order $order): View
    {
        $order = $this->orders->getOrderById($order->id);

        abort_if($order === null, 404);

        return view('v1.admin.orders.show', [
            'order' => $order,
            'orderRating' => \App\Models\OrderRating::where('order_id', $order->id)->first(),
        ]);
    }

    public function edit(Order $order): View
    {
        $order = $this->orders->getOrderById($order->id);

        abort_if($order === null, 404);

        return view('v1.admin.orders.edit', [
            'order' => $order,
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['status']) && $validated['status'] !== $order->status) {
            $order = $this->orders->updateOrderStatus($order, $validated['status']);
            unset($validated['status']);
        }

        if (isset($validated['payment_status']) && $validated['payment_status'] !== $order->payment_status) {
            $order = $this->orders->updateOrderPaymentStatus($order, $validated['payment_status']);
            unset($validated['payment_status']);
        }

        // Handle customer info and address snapshot only if order is pending or processing
        if (in_array($order->status, ['pending', 'processing'], true)) {
            $customerInfoUpdated = false;
            if (array_key_exists('customer_name', $validated) && $validated['customer_name'] !== $order->customer_name) {
                $customerInfoUpdated = true;
            }
            if (array_key_exists('customer_phone', $validated) && $validated['customer_phone'] !== $order->customer_phone) {
                $customerInfoUpdated = true;
            }
            if ($customerInfoUpdated) {
                $this->orders->logOrderEvent($order, 'customer_info_updated', null, null, auth()->id());
            }

            if (!empty($validated['remove_address'])) {
                if (!empty($order->shipping_address_snapshot)) {
                    $this->orders->logOrderEvent($order, 'address_removed', null, null, auth()->id());
                }
                $validated['shipping_address_snapshot'] = null;
            } else {
                $snapshot = $order->shipping_address_snapshot ?? [];
                $snapshotUpdated = false;

                $fields = ['name', 'phone', 'address', 'city', 'state'];
                foreach ($fields as $field) {
                    $key = "shipping_{$field}";
                    if (array_key_exists($key, $validated) && $validated[$key] !== ($snapshot[$field] ?? null)) {
                        $snapshot[$field] = $validated[$key];
                        $snapshotUpdated = true;
                    }
                }

                if ($snapshotUpdated) {
                    $validated['shipping_address_snapshot'] = $snapshot;
                    if (empty($order->shipping_address_snapshot)) {
                        $this->orders->logOrderEvent($order, 'address_added', null, null, auth()->id());
                    } else {
                        $this->orders->logOrderEvent($order, 'address_updated', null, null, auth()->id());
                    }
                }
            }
        } else {
            // Do not allow updating customer details if status is beyond processing
            unset($validated['customer_name'], $validated['customer_phone']);
        }

        // Always clean up individual shipping fields before DB update
        unset(
            $validated['shipping_name'],
            $validated['shipping_phone'],
            $validated['shipping_address'],
            $validated['shipping_city'],
            $validated['shipping_state'],
            $validated['remove_address']
        );

        if ($validated !== []) {
            $this->orders->update($order, $validated);
            $order = $order->fresh();
        }

        return redirect()
            ->route('v1.admin.orders.show', $order)
            ->with('success', __('messages.Order updated successfully.'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        if ($request->has('cancellation_reason') && $request->input('status') === 'cancelled') {
            $this->orders->update($order, ['cancellation_reason' => $request->input('cancellation_reason')]);
            $order = $order->fresh();
        }

        $order = $this->orders->updateOrderStatus($order, $request->validated('status'));

        return redirect()
            ->route('v1.admin.orders.show', $order)
            ->with('success', __('messages.Order status updated successfully.'));
    }

    public function updatePaymentStatus(UpdateOrderPaymentStatusRequest $request, Order $order): RedirectResponse
    {
        $order = $this->orders->updateOrderPaymentStatus($order, $request->validated('payment_status'));

        return redirect()
            ->route('v1.admin.orders.show', $order)
            ->with('success', __('messages.Payment status updated successfully.'));
    }

    public function invoice(Order $order): Response
    {
        $order = $this->orders->getOrderById($order->id);

        abort_if($order === null, 404);

        $logo = setting('app_logo');
        $logoPath = is_string($logo) && $logo !== ''
            ? public_path('storage/'.$logo)
            : null;
        $hasWatermarkLogo = $logoPath !== null && is_file($logoPath);

        $pdfConfig = array_merge(config('pdf'), [
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 12,
            'margin_header' => 0,
            'margin_footer' => 6,
        ]);

        // Crucial: Bind properties to the mPDF instance directly inside the callback
        $pdfConfig['instanceConfigurator'] = function ($mpdf) use ($hasWatermarkLogo, $logoPath): void {
            if ($hasWatermarkLogo && $logoPath !== null) {
                // SetWatermarkImage parameters: ($src, $alpha/opacity, $size, $position)
                // 'D' scales to fit page proportions, 'P' centers it on the full page
                $mpdf->SetWatermarkImage($logoPath, 0.07, 'D', 'P');
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImgBehind = true; // Prevents the watermark from washing out or overlapping your text
            }

            $mpdf->setAutoBottomMargin = 'pad';
            $mpdf->margin_footer = 6;
        };

        $pdf = PDF::loadView('v1.admin.orders.invoice', [
            'order' => $order,
            'asPdf' => true,
        ], [], $pdfConfig);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="order-invoice-'.$order->id.'.pdf"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderFilters(Request $request): array
    {
        return $request->only([
            'search',
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
