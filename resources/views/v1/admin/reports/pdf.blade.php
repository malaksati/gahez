@php
    $locale = app()->getLocale();
    $htmlLang = str_replace('_', '-', $locale);
    $isRtl = str_starts_with(strtolower($locale), 'ar');
    $appLogo = setting('app_logo', config('app.logo', ''));
    $formatDate = fn (?string $date) => $date ? \Carbon\Carbon::parse($date)->format('d-m-Y') : null;
    $dateFilteredTypes = [
        'sales-period',
        'sales-payment-methods',
        'top-products-categories',
        'deliveries',
        'zones-demand',
        'zones-activity',
    ];
    $hasDateRange = in_array($type, $dateFilteredTypes, true)
        && ! empty($filters['resolved_from'])
        && ! empty($filters['resolved_to']);
@endphp
<!doctype html>
<html lang="{{ $htmlLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <title>{{ $report['title'] }}</title>
    <style>
        @page {
            margin: 14mm 10mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Noto Sans Arabic', sans-serif;
            color: #111827;
            font-size: 13px;
            line-height: 1.5;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            unicode-bidi: embed;
        }

        .page {
            padding: 24px 20px 32px;
        }

        .report-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            border-bottom: 2px solid #e5e7eb;
        }

        .report-header td {
            vertical-align: middle;
            padding: 0 0 16px;
        }

        .report-header-title {
            text-align: start;
            width: 90%;
        }

        .report-header-logo {
            text-align: end;
            width: 10%;
        }

        .report-title {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }

        .logo {
            max-height: 52px;
            max-width: 160px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .meta-table td {
            padding: 6px 0;
            vertical-align: top;
        }

        .meta-table strong {
            color: #374151;
        }

        .meta-table .meta-end {
            text-align: end;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary-grid td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            font-size: 12px;
        }

        .summary-grid strong {
            display: block;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 2px;
        }

        table.report-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table.report-items th {
            background: #1e3a5f;
            color: #fff;
            border: 1px solid #1e3a5f;
            padding: 9px 8px;
            text-align: start;
            font-weight: 700;
        }

        table.report-items td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
            text-align: start;
        }

        table.report-items tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>

<body>
    @if (! empty($asPdf))
        <htmlpagefooter name="reportFooter">
            <div style="text-align:center; color:#6b7280; font-size:11px; direction:ltr;">
                {PAGENO} | {PAGENO}
            </div>
        </htmlpagefooter>
        <sethtmlpagefooter name="reportFooter" value="on" />
    @endif

    <div class="page">
        <table class="report-header">
            <tr>
                <td class="report-header-title">
                    <h1 class="report-title">{{ $report['title'] }}</h1>
                </td>
                <td class="report-header-logo">
                    @if ($appLogo && storage_public_path($appLogo))
                        <img src="{{ storage_public_path($appLogo) }}" alt="" class="logo">
                    @endif
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td>
                    <strong>{{ __('messages.Generated at') }}:</strong>
                    <span class="ltr">{{ now()->format('d-m-Y H:i') }}</span>
                </td>
                @if ($hasDateRange)
                    <td class="meta-end">
                        <strong>{{ __('messages.Date range') }}:</strong>
                        <span class="ltr">{{ $formatDate($filters['resolved_from']) }} — {{ $formatDate($filters['resolved_to']) }}</span>
                    </td>
                @endif
            </tr>
        </table>

        @if (! empty($report['summary']) && in_array($type, ['customers', 'sales-period', 'sales-payment-methods'], true))
            <table class="summary-grid">
                <tr>
                    @foreach (match ($type) {
                        'customers' => [
                            ['label' => __('messages.Total customers'), 'value' => $report['summary']['total'] ?? 0],
                            ['label' => __('messages.Active customers'), 'value' => $report['summary']['active'] ?? 0],
                            ['label' => __('messages.Inactive customers'), 'value' => $report['summary']['inactive'] ?? 0],
                        ],
                        'sales-period' => [
                            ['label' => __('messages.Orders'), 'value' => $report['summary']['orders_count'] ?? 0],
                            ['label' => __('messages.Revenue'), 'value' => format_local_number($report['summary']['revenue'] ?? 0, 2).' '.display_currency()],
                            ['label' => __('messages.Average demand'), 'value' => format_local_number($report['summary']['avg_order'] ?? 0, 2).' '.display_currency()],
                            ['label' => __('messages.Shipments'), 'value' => $report['summary']['shipments'] ?? 0],
                        ],
                        'sales-payment-methods' => [
                            ['label' => __('messages.Total orders'), 'value' => $report['summary']['total_orders'] ?? 0],
                            ['label' => __('messages.Total revenue'), 'value' => format_local_number($report['summary']['total_revenue'] ?? 0, 2).' '.display_currency()],
                        ],
                        default => [],
                    } as $item)
                        <td>
                            <strong>{{ $item['label'] }}</strong>
                            {{ $item['value'] }}
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif

        <table class="report-items">
            <thead>
                <tr>
                    @foreach ($report['headings'] as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report['headings']) }}" class="muted" style="text-align:center;">
                            {{ __('messages.No data.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
