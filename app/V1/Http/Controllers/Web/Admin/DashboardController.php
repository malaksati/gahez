<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends AdminController
{
    public function index(): View
    {
        $revenue = Order::query()->where('payment_status', 'paid')->sum('total');
        $ordersCount = Order::query()->count();
        $pendingOrders = Order::query()->where('status', 'pending')->count();
        $processingOrders = Order::query()->where('status', 'processing')->count();
        $ordersReadyForDelivery = Order::query()->where('status', 'ready_for_delivery')->count();
        $pendingRefundRequests = OrderRefundRequest::query()->where('status', 'pending')->count();
        $openTickets = Ticket::query()->where('status', 'pending')->count();
        $pendingProductReports = ProductReport::query()->where('status', 'pending')->count();
        $productsCount = Product::query()->count();
        $usersCount = User::query()->count();

        $stats = [
            [
                'label' => __('messages.Total revenue'),
                'value' => format_local_number((float) $revenue, 2),
                'suffix' => display_currency(),
                'description' => __('messages.From paid orders'),
                'icon' => 'currency',
                'color' => 'amber',
            ],
            [
                'label' => __('messages.Orders'),
                'value' => format_local_number($ordersCount),
                'description' => __('messages.:count pending', ['count' => $pendingOrders]),
                'icon' => 'cart',
                'color' => 'blue',
                'href' => route('v1.admin.orders.index'),
            ],
            [
                'label' => __('messages.Products'),
                'value' => format_local_number($productsCount),
                'description' => __('messages.In catalog'),
                'icon' => 'cube',
                'color' => 'emerald',
                'href' => route('v1.admin.products.index'),
            ],
            [
                'label' => __('messages.Customers'),
                'value' => format_local_number($usersCount),
                'description' => __('messages.Registered users'),
                'icon' => 'users',
                'color' => 'violet',
            ],
            [
                'label' => __('messages.Open tickets'),
                'value' => format_local_number($openTickets),
                'description' => __('messages.Needs attention'),
                'icon' => 'chat',
                'color' => 'rose',
                'href' => route('v1.admin.tickets.index'),
            ],
        ];

        $recentOrders = Order::query()
            ->with('user:id,name,email')
            ->latest()
            ->limit(8)
            ->get();

        $user = Auth::user();
        $canManageOrders = $user?->can('manage orders') ?? false;
        $canManageRefunds = $user?->can('manage refunds') ?? false;
        $canManageTickets = $user?->can('manage tickets') ?? false;
        $canManageProductReports = $user?->can('manage product-reports') ?? false;

        $hasPendingActions = ($canManageOrders && ($pendingOrders > 0 || $processingOrders > 0 || $ordersReadyForDelivery > 0))
            || ($canManageRefunds && $pendingRefundRequests > 0)
            || ($canManageTickets && $openTickets > 0)
            || ($canManageProductReports && $pendingProductReports > 0);

        return view('v1.admin.dashboard', compact(
            'stats',
            'recentOrders',
            'pendingOrders',
            'processingOrders',
            'ordersReadyForDelivery',
            'pendingRefundRequests',
            'openTickets',
            'pendingProductReports',
            'canManageOrders',
            'canManageRefunds',
            'canManageTickets',
            'canManageProductReports',
            'hasPendingActions',
        ));
    }
}
