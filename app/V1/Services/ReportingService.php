<?php

namespace App\V1\Services;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Admin analytics report. `top_products` contains at most one row: best-selling product by units in paid orders.
     *
     * @return array{
     *   from: CarbonImmutable,
     *   to: CarbonImmutable,
     *   kpis: array<string, float|int>,
     *   top_products: Collection<int, array{product_id:int, product_name:string, quantity:int, revenue:float}>,
     *   daily_sales: Collection<int, array{date:string, total:float}>,
     * }
     */
    public function adminReport(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $paidOrdersQuery = $this->paidOrdersBetween($from, $to);

        $paidOrdersCount = (int) (clone $paidOrdersQuery)->count();
        $paidOrdersTotal = (float) (clone $paidOrdersQuery)->sum('total');
        $deliveredPaidOrdersCount = (int) (clone $paidOrdersQuery)->where('status', 'delivered')->count();
        $totalCommission = (float) (clone $paidOrdersQuery)->sum('total_commission');

        $refundedOrdersQuery = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where(function ($query) {
                $query->where('payment_status', 'refunded')
                    ->orWhere('status', 'refunded');
            });

        $refundedOrdersCount = (int) (clone $refundedOrdersQuery)->count();
        $refundedTotal = (float) (clone $refundedOrdersQuery)->sum('refunded_total');

        $pendingRefundRequests = (int) OrderRefundRequest::query()->where('status', 'pending')->count();

        $topProducts = $this->topSellingProduct($from, $to);

        $dailySales = $this->dailyPaidSales($from, $to);

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'paid_orders_count' => $paidOrdersCount,
                'paid_orders_total' => round($paidOrdersTotal, 2),
                'delivered_paid_orders_count' => $deliveredPaidOrdersCount,
                'total_commission' => round($totalCommission, 2),
                'refunded_orders_count' => $refundedOrdersCount,
                'refunded_total' => round($refundedTotal, 2),
                'pending_refund_requests' => $pendingRefundRequests,
            ],
            'top_products' => $topProducts,
            'daily_sales' => $dailySales,
        ];
    }

    /**
     * @param  array{
     *   product_id?: int|string|null,
     *   category_id?: int|string|null,
     *   payment_status?: string|null,
     *   order_status?: string|null,
     * }  $filters
     * @return array{
     *   from: CarbonImmutable,
     *   to: CarbonImmutable,
     *   kpis: array<string, float|int>,
     *   daily_sales: Collection<int, array{date:string, total:float, qty:int}>,
     * }
     */
    public function productPerformance(CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $query = Order::query()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        } else {
            $query->where('payment_status', 'paid');
        }

        if (! empty($filters['order_status'])) {
            $query->where('status', $filters['order_status']);
        }

        $revenue = (float) (clone $query)->sum('total');
        $ordersCount = (int) (clone $query)->count();
        $commission = (float) (clone $query)->sum('total_commission');
        $net = max(0, $revenue - $commission);

        $refundedQuery = (clone $query)->where(function ($q) {
            $q->where('payment_status', 'refunded')->orWhere('status', 'refunded');
        });

        $refundedAmount = (float) (clone $refundedQuery)->sum('refunded_total');
        $refundedOrders = (int) (clone $refundedQuery)->count();

        $dailySales = DB::table('orders')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->when(
                ! empty($filters['payment_status']),
                fn ($q) => $q->where('payment_status', $filters['payment_status']),
                fn ($q) => $q->where('payment_status', 'paid'),
            )
            ->when(
                ! empty($filters['order_status']),
                fn ($q) => $q->where('status', $filters['order_status']),
            )
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as qty')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'total' => round((float) $row->total, 2),
                'qty' => (int) $row->qty,
            ]);

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'revenue' => round($revenue, 2),
                'orders_count' => $ordersCount,
                'quantity' => $ordersCount,
                'commission' => round($commission, 2),
                'net' => round($net, 2),
                'refunded_amount' => round($refundedAmount, 2),
                'refunded_orders' => $refundedOrders,
            ],
            'daily_sales' => $dailySales,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Order>
     */
    protected function paidOrdersBetween(CarbonImmutable $from, CarbonImmutable $to)
    {
        return Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('payment_status', 'paid');
    }

    /**
     * @return Collection<int, array{date:string, total:float}>
     */
    protected function dailyPaidSales(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'total' => round((float) $row->total, 2),
            ]);
    }

    /**
     * Single best-selling product in paid orders for the date range (by units sold, then revenue).
     *
     * @return Collection<int, array{product_id:int, product_name:string, quantity:int, revenue:float}>
     */
    protected function topSellingProduct(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $locale = app()->getLocale();
        $nameSql = str_starts_with(strtolower($locale), 'ar')
            ? 'COALESCE(MAX(oi.product_name_ar), MAX(oi.product_name))'
            : 'COALESCE(MAX(oi.product_name), MAX(oi.product_name_ar))';

        $row = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.payment_status', 'paid')
            ->whereBetween('o.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->whereNotNull('oi.product_id')
            ->groupBy('oi.product_id')
            ->selectRaw('oi.product_id')
            ->selectRaw('SUM(oi.quantity) as quantity')
            ->selectRaw('SUM(oi.unit_price * oi.quantity - COALESCE(oi.line_discount, 0)) as revenue')
            ->selectRaw("{$nameSql} as product_name")
            ->orderByDesc('quantity')
            ->orderByDesc('revenue')
            ->first();

        if ($row === null) {
            return collect();
        }

        return collect([
            [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) ($row->product_name ?: '—'),
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ],
        ]);
    }
}
