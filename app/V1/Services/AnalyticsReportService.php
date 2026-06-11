<?php

namespace App\V1\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsReportService
{
    /**
     * @return array{
     *   title: string,
     *   headings: list<string>,
     *   rows: list<list<mixed>>,
     *   summary: array<string, mixed>,
     *   meta: array<string, mixed>,
     * }
     */
    public function build(string $type, array $filters = []): array
    {
        [$from, $to] = $this->resolveDateRange($filters);

        return match ($type) {
            'customers' => $this->customersReport(),
            'sales-period' => $this->salesPeriodReport($from, $to, $filters),
            'sales-payment-methods' => $this->salesPaymentMethodsReport($from, $to),
            'top-products-categories' => $this->topProductsCategoriesReport($from, $to),
            'stock' => $this->stockReport(),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };
    }

    /**
     * Overview chart data for the reports hub (last 30 days, display only).
     *
     * @return array{
     *   period_label: string,
     *   revenue_trend: array{labels: list<string>, values: list<float>},
     *   orders_trend: array{labels: list<string>, values: list<int>},
     *   payment_methods: array{labels: list<string>, values: list<float>},
     *   top_products: array{labels: list<string>, values: list<int>},
     * }
     */
    public function chartOverview(): array
    {
        $to = CarbonImmutable::today()->endOfDay();
        $from = $to->copy()->subDays(29)->startOfDay();
        $dateKeys = $this->chartDateKeys($from, $to);
        $labels = $dateKeys->map(
            fn (string $date) => CarbonImmutable::parse($date)
                ->locale(app()->getLocale())
                ->translatedFormat('d M')
        )->all();

        $revenueRows = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'date');

        $orderRows = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date');

        $locale = app()->getLocale();
        $nameSql = str_starts_with(strtolower($locale), 'ar')
            ? 'COALESCE(MAX(oi.product_name_ar), MAX(oi.product_name))'
            : 'COALESCE(MAX(oi.product_name), MAX(oi.product_name_ar))';

        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.payment_status', 'paid')
            ->whereBetween('o.created_at', [$from, $to])
            ->whereNotNull('oi.product_id')
            ->groupBy('oi.product_id')
            ->selectRaw("{$nameSql} as product_name")
            ->selectRaw('SUM(oi.quantity) as quantity')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        $paymentRows = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_method')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        $periodLabel = $from->translatedFormat('d M Y').' — '.$to->translatedFormat('d M Y');

        return [
            'period_label' => $periodLabel,
            'revenue_trend' => [
                'labels' => $labels,
                'values' => $dateKeys->map(fn (string $date) => round((float) ($revenueRows[$date] ?? 0), 2))->all(),
                'period_label' => $periodLabel,
            ],
            'orders_trend' => [
                'labels' => $labels,
                'values' => $dateKeys->map(fn (string $date) => (int) ($orderRows[$date] ?? 0))->all(),
                'period_label' => $periodLabel,
            ],
            'payment_methods' => [
                'labels' => $paymentRows->map(fn ($row) => $this->formatPaymentMethod((string) $row->payment_method))->all(),
                'values' => $paymentRows->map(fn ($row) => round((float) $row->revenue, 2))->all(),
            ],
            'top_products' => [
                'labels' => $topProducts->map(fn ($row) => (string) ($row->product_name ?: '—'))->all(),
                'values' => $topProducts->map(fn ($row) => (int) $row->quantity)->all(),
            ],
        ];
    }

    /**
     * Daily paid-order counts for dashboard charts (longer range than chart overview).
     *
     * @return array{
     *   labels: list<string>,
     *   values: list<int>,
     *   period_label: string,
     * }
     */
    public function dailyOrdersTrend(int $days = 90): array
    {
        $days = max(7, $days);
        $to = CarbonImmutable::today()->endOfDay();
        $from = $to->copy()->subDays($days - 1)->startOfDay();

        return $this->buildPaidOrdersDailyTrend($from, $to);
    }

    /**
     * @return array{labels: list<string>, values: list<int>, period_label: string}
     */
    protected function buildPaidOrdersDailyTrend(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $dateKeys = $this->chartDateKeys($from, $to);
        $labels = $dateKeys->map(
            fn (string $date) => CarbonImmutable::parse($date)
                ->locale(app()->getLocale())
                ->translatedFormat('d M')
        )->all();

        $orderRows = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date');

        return [
            'labels' => $labels,
            'values' => $dateKeys->map(fn (string $date) => (int) ($orderRows[$date] ?? 0))->all(),
            'period_label' => $from->translatedFormat('d M Y').' — '.$to->translatedFormat('d M Y'),
        ];
    }

    /**
     * @return list<array{key: string, title: string, description: string, icon: string}>
     */
    public function availableReports(): array
    {
        return [
            ['key' => 'customers', 'title' => __('messages.Customers report'), 'description' => __('messages.Customers report description'), 'icon' => 'bi-people'],
            ['key' => 'sales-period', 'title' => __('messages.Sales period report'), 'description' => __('messages.Sales period report description'), 'icon' => 'bi-graph-up'],
            ['key' => 'sales-payment-methods', 'title' => __('messages.Sales by payment method'), 'description' => __('messages.Sales payment methods report description'), 'icon' => 'bi-credit-card'],
            ['key' => 'top-products-categories', 'title' => __('messages.Top products and categories'), 'description' => __('messages.Top products categories report description'), 'icon' => 'bi-trophy'],
            ['key' => 'stock', 'title' => __('messages.Stock report'), 'description' => __('messages.Stock report description'), 'icon' => 'bi-boxes'],
        ];
    }

    protected function customersReport(): array
    {
        $customers = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'is_active']);

        $rows = $customers->map(fn (User $user) => [
            $user->name,
            $user->email ?? '—',
            $user->phone ?? '—',
            $user->is_active ? __('messages.Active') : __('messages.Inactive'),
        ])->all();

        return [
            'title' => __('messages.Customers report'),
            'headings' => [
                __('messages.Name'),
                __('messages.Email'),
                __('messages.Phone'),
                __('messages.Status'),
            ],
            'rows' => $rows,
            'summary' => [
                'total' => $customers->count(),
                'active' => $customers->where('is_active', true)->count(),
                'inactive' => $customers->where('is_active', false)->count(),
            ],
            'meta' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function salesPeriodReport(CarbonImmutable $from, CarbonImmutable $to, array $filters): array
    {
        $periodType = (string) ($filters['period_type'] ?? 'monthly');
        $groupFormat = $this->salesGroupingFormat($periodType, $from, $to);

        $rows = DB::table('orders')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('payment_status', 'paid')
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as period")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as revenue')
            ->selectRaw('AVG(total) as avg_order')
            ->selectRaw("SUM(CASE WHEN status IN ('shipped', 'delivered') THEN 1 ELSE 0 END) as shipments")
            ->selectRaw('SUM(total_shipping) as shipping_revenue')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => [
                (string) $row->period,
                (int) $row->orders_count,
                round((float) $row->revenue, 2),
                round((float) $row->avg_order, 2),
                (int) $row->shipments,
                round((float) $row->shipping_revenue, 2),
            ])
            ->all();

        $totals = DB::table('orders')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('payment_status', 'paid')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as revenue')
            ->selectRaw('AVG(total) as avg_order')
            ->selectRaw("SUM(CASE WHEN status IN ('shipped', 'delivered') THEN 1 ELSE 0 END) as shipments")
            ->selectRaw('SUM(total_shipping) as shipping_revenue')
            ->first();

        return [
            'title' => __('messages.Sales period report'),
            'headings' => [
                __('messages.Period'),
                __('messages.Orders'),
                __('messages.Revenue'),
                __('messages.Average demand'),
                __('messages.Shipments'),
                __('messages.Shipping revenue'),
            ],
            'rows' => $rows,
            'summary' => [
                'orders_count' => (int) ($totals->orders_count ?? 0),
                'revenue' => round((float) ($totals->revenue ?? 0), 2),
                'avg_order' => round((float) ($totals->avg_order ?? 0), 2),
                'shipments' => (int) ($totals->shipments ?? 0),
                'shipping_revenue' => round((float) ($totals->shipping_revenue ?? 0), 2),
                'period_type' => $periodType,
            ],
            'meta' => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'period_type' => $periodType],
        ];
    }

    protected function salesPaymentMethodsReport(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $methods = ['cash_on_delivery', 'knet', 'visa', 'applepay', 'apple_pay', 'wallet'];

        $rows = DB::table('orders')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('payment_status', 'paid')
            ->whereIn('payment_method', $methods)
            ->selectRaw('payment_method')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                $this->formatPaymentMethod((string) $row->payment_method),
                (int) $row->orders_count,
                round((float) $row->revenue, 2),
            ])
            ->all();

        return [
            'title' => __('messages.Sales by payment method'),
            'headings' => [
                __('messages.Payment method'),
                __('messages.Orders'),
                __('messages.Revenue'),
            ],
            'rows' => $rows,
            'summary' => [
                'total_orders' => array_sum(array_column($rows, 1)),
                'total_revenue' => round(array_sum(array_map(fn ($r) => $r[2], $rows)), 2),
            ],
            'meta' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ];
    }

    protected function topProductsCategoriesReport(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $locale = app()->getLocale();
        $nameSql = str_starts_with(strtolower($locale), 'ar')
            ? 'COALESCE(MAX(oi.product_name_ar), MAX(oi.product_name))'
            : 'COALESCE(MAX(oi.product_name), MAX(oi.product_name_ar))';

        $productRows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.payment_status', 'paid')
            ->whereBetween('o.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->whereNotNull('oi.product_id')
            ->groupBy('oi.product_id', 'oi.product_sku')
            ->selectRaw('oi.product_sku')
            ->selectRaw("{$nameSql} as product_name")
            ->selectRaw('SUM(oi.quantity) as quantity')
            ->selectRaw('SUM(oi.unit_price * oi.quantity - COALESCE(oi.line_discount, 0)) as revenue')
            ->orderByDesc('quantity')
            ->limit(100)
            ->get()
            ->map(fn ($row) => [
                (string) ($row->product_name ?: '—'),
                (string) ($row->product_sku ?: '—'),
                (int) $row->quantity,
                round((float) $row->revenue, 2),
                __('messages.Product'),
            ])
            ->all();

        $categoryLocale = str_starts_with(strtolower($locale), 'ar') ? 'ar' : 'en';

        $categoryRows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_categories as pc', 'pc.product_id', '=', 'oi.product_id')
            ->join('categories as c', 'c.id', '=', 'pc.category_id')
            ->where('o.payment_status', 'paid')
            ->whereBetween('o.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->groupBy('c.id')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(c.name, '$.{$categoryLocale}')) as category_name")
            ->selectRaw('SUM(oi.quantity) as quantity')
            ->selectRaw('SUM(oi.unit_price * oi.quantity - COALESCE(oi.line_discount, 0)) as revenue')
            ->orderByDesc('quantity')
            ->limit(100)
            ->get()
            ->map(fn ($row) => [
                (string) ($row->category_name ?: '—'),
                '—',
                (int) $row->quantity,
                round((float) $row->revenue, 2),
                __('messages.Category'),
            ])
            ->all();

        $rows = array_merge($productRows, $categoryRows);

        return [
            'title' => __('messages.Top products and categories'),
            'headings' => [
                __('messages.Name'),
                __('messages.SKU'),
                __('messages.Quantity sold'),
                __('messages.Revenue'),
                __('messages.Type'),
            ],
            'rows' => $rows,
            'summary' => ['products' => count($productRows), 'categories' => count($categoryRows)],
            'meta' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ];
    }

    protected function stockReport(): array
    {
        $rows = [];

        Product::query()
            ->with(['variants' => fn ($q) => $q->orderBy('id')])
            ->orderBy('sku')
            ->chunk(200, function ($products) use (&$rows) {
                foreach ($products as $product) {
                    if ($product->isVariable() && $product->variants->isNotEmpty()) {
                        foreach ($product->variants as $variant) {
                            $rows[] = $this->stockRow($product, $variant);
                        }
                    } else {
                        $rows[] = $this->stockRow($product, null);
                    }
                }
            });

        return [
            'title' => __('messages.Stock report'),
            'headings' => [
                __('messages.Product'),
                __('messages.SKU'),
                __('messages.Variant'),
                __('messages.Stock'),
                __('messages.Availability'),
                __('messages.Status'),
            ],
            'rows' => $rows,
            'summary' => ['total_lines' => count($rows)],
            'meta' => [],
        ];
    }

    /**
     * @return list<mixed>
     */
    protected function stockRow(Product $product, ?ProductVariant $variant): array
    {
        $name = $product->getTranslation('name', app()->getLocale(), false)
            ?: $product->getTranslation('name', 'en', false)
            ?: $product->sku;

        $tracksStock = $variant ? $variant->tracksStock() : $product->tracksStock();
        $inStock = $variant ? $variant->isInStock() : $product->isInStock();

        return [
            $name,
            $variant?->sku ?? $product->sku,
            $variant?->getTranslation('name', 'en', false) ?? '—',
            $tracksStock ? ($variant?->stock ?? $product->stock ?? 0) : __('messages.Untracked'),
            $inStock ? __('messages.Available') : __('messages.Out of stock'),
            $product->is_active ? __('messages.Active') : __('messages.Inactive'),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    protected function chartDateKeys(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $dates = collect();

        for ($day = $from; $day->lte($to); $day = $day->addDay()) {
            $dates->push($day->toDateString());
        }

        return $dates;
    }

    protected function formatPaymentMethod(string $method): string
    {
        return match (strtolower($method)) {
            'cash_on_delivery' => __('messages.Cash on delivery'),
            'knet' => 'KNET',
            'visa' => 'Visa',
            'applepay', 'apple_pay' => 'Apple Pay',
            'wallet' => __('messages.Wallet'),
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function resolveDateRange(array $filters): array
    {
        $periodType = (string) ($filters['period_type'] ?? 'monthly');
        $today = CarbonImmutable::today();

        return match ($periodType) {
            'daily' => [$today->startOfDay(), $today->endOfDay()],
            'weekly' => [$today->subDays(6)->startOfDay(), $today->endOfDay()],
            'monthly' => [$today->subMonth()->startOfDay(), $today->endOfDay()],
            default => [
                ! empty($filters['from_date'])
                    ? CarbonImmutable::parse($filters['from_date'])->startOfDay()
                    : $today->subMonth()->startOfDay(),
                ! empty($filters['to_date'])
                    ? CarbonImmutable::parse($filters['to_date'])->endOfDay()
                    : $today->endOfDay(),
            ],
        };
    }

    protected function salesGroupingFormat(string $periodType, CarbonImmutable $from, CarbonImmutable $to): string
    {
        if ($periodType !== 'manual') {
            return '%Y-%m-%d';
        }

        return $from->diffInDays($to) > 62 ? '%Y-%m' : '%Y-%m-%d';
    }
}
