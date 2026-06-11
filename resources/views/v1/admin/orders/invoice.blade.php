@php
    $locale = app()->getLocale();
    $currency = display_currency();
    $discountTotal = (float) $order->order_discount + (float) $order->coupon_discount;
    $htmlLang = str_replace('_', '-', $locale);
    $isRtl = str_starts_with(strtolower($locale), 'ar');
    $appLogo = setting('app_logo', config('app.logo', ''));
@endphp
<!doctype html>
<html lang="{{ $htmlLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <title>{{ __('messages.Invoice') }} #{{ $order->id }}</title>
    <style>
        @page {
            margin: 8mm 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Noto Sans Arabic', sans-serif;
            color: #111827;
            font-size: 14px;
            line-height: 1.35;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            unicode-bidi: embed;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }

        .page {
            padding: 12px 16px 16px;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .print-footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            z-index: 10;
        }

        .no-print {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            color: #111827;
            text-decoration: none;
            font-size: 13px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .logo {
            height: 44px;
            margin-bottom: 4px;
        }

        .title {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.02em;
            color: #0f172a;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
            text-align: start;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table strong {
            color: #374151;
            font-weight: 700;
        }

        .info-table td.col-end {
            text-align: end;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .label-box {
            background: #1e3a5f;
            color: #fff;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.03em;
            margin: 6px 0 4px;
            display: block;
            text-align: start;
        }

        .address-block {
            line-height: 1.35;
            margin-bottom: 8px;
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            color: #1f2937;
        }

        .product-meta {
            margin-top: 2px;
            font-size: 13px;
            color: #6b7280;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 13px;
        }

        table.items th {
            background: #1e3a5f;
            color: #fff;
            border: 1px solid #1e3a5f;
            padding: 5px 8px;
            text-align: start;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        table.items td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            vertical-align: top;
            text-align: start;
        }

        table.items tbody tr:nth-child(even) {
            background: #fafafa;
        }

        table.items tbody tr:nth-child(odd) {
            background: #fff;
        }

        .text-end {
            text-align: end;
        }

        .text-center {
            text-align: center;
        }

        .page-shell {
            position: relative;
            z-index: 5;
        }

        table.totals-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            background: #fff;
            font-size: 14px;
        }

        table.totals-table td {
            padding: 5px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        table.totals-table tr:last-child td {
            border-bottom: none;
        }

        table.totals-table td.totals-label {
            text-align: start;
            width: 52%;
            font-weight: 600;
            color: #374151;
        }

        table.totals-table td.totals-value {
            text-align: end;
            font-weight: 700;
            white-space: nowrap;
            font-size: 14px;
        }

        table.totals-table tr.totals-line-muted td.totals-label {
            font-weight: 500;
            color: #6b7280;
        }

        table.totals-table tr.totals-grand td {
            padding-top: 6px;
            padding-bottom: 6px;
            background: #eef2ff;
            border-top: 2px solid #a5b4fc;
        }

        table.totals-table tr.totals-grand td.totals-label {
            font-size: 16px;
            font-weight: 800;
            color: #1e1b4b;
        }

        table.totals-table tr.totals-grand td.totals-value {
            font-size: 16px;
            font-weight: 800;
            color: #1e1b4b;
        }

        table.totals-table tr:nth-child(odd):not(.totals-grand) {
            background: #fafafa;
        }

        table.totals-table tr:nth-child(odd):not(.totals-grand) td {
            background: #fafafa;
        }

        .danger {
            color: #b91c1c;
            font-weight: 600;
        }

        .ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        @media screen {
            .print-footer {
                display: none;
            }
        }
    </style>
</head>

<body>
    @if (! empty($asPdf))
        <htmlpagefooter name="invoiceFooter">
            <div style="text-align:center; color:#6b7280; font-size:12px; direction:ltr;">
                {PAGENO} | {PAGENO}
            </div>
        </htmlpagefooter>
        <sethtmlpagefooter name="invoiceFooter" value="on" />
    @endif
    <div class="page-shell">
        <div class="page">
            @empty($asPdf)
                <div class="no-print">
                    <span class="muted">{{ __('messages.Print or save as PDF from your browser.') }}</span>
                    <a class="btn" href="{{ route('v1.admin.orders.show', $order) }}">{{ __('messages.Back') }}</a>
                </div>
            @endempty

            <div class="header">
                @if ($appLogo)
                    @php($logoSrc = empty($asPdf) ? storage_public_url($appLogo) : storage_public_path($appLogo))
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" alt="" class="logo">
                    @endif
                @endif
                <h1 class="title">{{ __('messages.Invoice') }}</h1>
            </div>

            {{-- ... keeps rest of your content intact ... --}}
            <table class="info-table">
                <tr>
                    <td>
                        <strong>{{ __('messages.Invoice ID') }}:</strong>
                        <span class="muted"><span class="ltr">#{{ $order->id }}</span></span>
                    </td>
                    <td class="col-end">
                        <strong>{{ __('messages.Order date') }}:</strong>
                        <span class="muted"><span
                                class="ltr">{{ $order->created_at?->format('d-m-Y') }}</span></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ __('messages.Customer') }}:</strong>
                        <span class="muted">{{ $order->user->name ?? $order->customer_name ?? '—' }}</span>
                    </td>
                    <td class="col-end">
                        <strong>{{ __('messages.Phone') }}:</strong>
                        <span class="muted"><span
                                class="ltr">{{ $order->address->phone ?? (($order->shipping_address_snapshot['phone'] ?? null) ?: ($order->customer_phone ?? ($order->user->phone ?? '—'))) }}</span></span>
                    </td>
                </tr>
            </table>

            @include('v1.admin.orders.partials.payment-invoice-details', ['order' => $order])

            <div class="label-box">{{ __('messages.Bill & ship to') }}</div>
            <div class="address-block">
                {{ $order->user->name ?? $order->customer_name ?? '—' }}<br>
                @if ($order->address)
                    {{ $order->address->address }}<br>
                    {{ $order->address->city ?? '' }}@if ($order->address->state)
                        , {{ $order->address->state }}
                    @endif
                    <br>
                    <span class="ltr">{{ $order->address->phone ?? '' }}</span>
                @elseif (! empty($order->shipping_address_snapshot))
                    {{ $order->shipping_address_snapshot['address'] ?? '—' }}<br>
                    {{ $order->shipping_address_snapshot['city'] ?? '' }}@if (! empty($order->shipping_address_snapshot['state']))
                        , {{ $order->shipping_address_snapshot['state'] }}
                    @endif
                    <br>
                    <span class="ltr">{{ $order->shipping_address_snapshot['phone'] ?? '' }}</span>
                @else
                    <span class="muted">—</span>
                @endif
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th>{{ __('messages.SKU') }}</th>
                        <th>{{ __('messages.Product') }}</th>
                        <th class="text-end">{{ __('messages.Price') }}</th>
                        <th class="text-center">{{ __('messages.Qty') }}</th>
                        <th class="text-end">{{ __('messages.Sub total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        @php
                            $lineTotal = ((float) $item->unit_price * (int) $item->quantity)
                                - (float) $item->line_discount;
                            $productName = $item->product
                                ? ($item->product->getTranslation('name', $locale, false) ?:
                                $item->product->getTranslation('name', 'en', false))
                                : (($locale === 'ar' ? $item->product_name_ar : null) ?: $item->product_name ?: '—');
                            $variantName = $item->variant
                                ? ($item->variant->getTranslation('name', $locale, false) ?:
                                $item->variant->getTranslation('name', 'en', false))
                                : (($locale === 'ar' ? $item->variant_name_ar : null) ?: $item->variant_name ?: null);
                        @endphp
                        <tr>
                            <td><span class="ltr">{{ optional($item->variant)->sku ?? ($item->variant_sku ?? (optional($item->product)->sku ?? ($item->product_sku ?? '—'))) }}</span>
                            </td>
                            <td>
                                {{ $productName }}
                                @if ($variantName)
                                    <div class="product-meta">{{ $variantName }}</div>
                                @endif
                            </td>
                            <td class="text-end"><span class="ltr">{{ format_local_number((float) $item->unit_price, 2) }}
                                    {{ $currency }}</span></td>
                            <td class="text-center"><span class="ltr">@num($item->quantity)</span></td>
                            <td class="text-end"><strong><span class="ltr">{{ format_local_number($lineTotal, 2) }}
                                        {{ $currency }}</span></strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals-table">
                <tr class="totals-line-muted">
                    <td class="totals-label">{{ __('messages.Sub total') }}</td>
                    <td class="totals-value"><span class="ltr">{{ format_local_number((float) $order->sub_total, 2) }}
                            {{ $currency }}</span></td>
                </tr>
                @if ($discountTotal > 0)
                    <tr class="totals-line-muted">
                        <td class="totals-label">{{ __('messages.Discount') }}</td>
                        <td class="totals-value danger"><span class="ltr">-{{ format_local_number($discountTotal, 2) }}
                                {{ $currency }}</span></td>
                    </tr>
                @endif
                @if ((float) $order->coupon_discount > 0 && $order->coupon)
                    <tr class="totals-line-muted">
                        <td class="totals-label">{{ __('messages.Coupon') }} (<span
                                class="ltr">{{ $order->coupon->code }}</span>)</td>
                        <td class="totals-value danger"><span
                                class="ltr">-{{ format_local_number((float) $order->coupon_discount, 2) }}
                                {{ $currency }}</span></td>
                    </tr>
                @endif
                @if ((float) $order->wallet_used > 0)
                    <tr class="totals-line-muted">
                        <td class="totals-label">{{ __('messages.Wallet used') }}</td>
                        <td class="totals-value danger"><span
                                class="ltr">-{{ format_local_number((float) $order->wallet_used, 2) }}
                                {{ $currency }}</span></td>
                    </tr>
                @endif
                <tr class="totals-line-muted">
                    <td class="totals-label">{{ __('messages.Shipping') }}</td>
                    <td class="totals-value"><span
                            class="ltr">{{ format_local_number((float) $order->total_shipping, 2) }}
                            {{ $currency }}</span></td>
                </tr>
                <tr class="totals-grand">
                    <td class="totals-label">{{ __('messages.Grand total') }}</td>
                    <td class="totals-value"><span class="ltr">{{ format_local_number((float) $order->total, 2) }}
                            {{ $currency }}</span></td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
