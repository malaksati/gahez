<?php

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:backfill-item-snapshots {--chunk=500} {--dry-run} {--force}', function () {
    if (! Schema::hasColumns('order_items', [
        'product_name',
        'product_name_ar',
        'product_slug',
        'product_sku',
        'variant_name',
        'variant_name_ar',
        'variant_sku',
    ])) {
        $this->error('Snapshot columns do not exist on order_items. Run migrations first.');

        return self::FAILURE;
    }

    $chunkSize = max(1, (int) $this->option('chunk'));
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');
    $updated = 0;
    $skipped = 0;

    $query = OrderItem::query()->orderBy('id');

    if (! $force) {
        $query->where(function ($q) {
            $q->whereNull('product_name')
                ->orWhereNull('product_slug')
                ->orWhereNull('product_sku');
        });
    }

    $total = (clone $query)->count();
    $this->info("Processing {$total} order items...");

    $query->chunkById($chunkSize, function ($items) use (&$updated, &$skipped, $dryRun) {
        foreach ($items as $item) {
            $product = Product::withTrashed()->find($item->product_id);
            $variant = $item->variant_id
                ? ProductVariant::withTrashed()->find($item->variant_id)
                : null;

            if (! $product) {
                $skipped++;
                continue;
            }

            $productNameEn = $product->getTranslation('name', 'en', false);
            $productNameAr = $product->getTranslation('name', 'ar', false);
            $variantNameEn = $variant?->getTranslation('name', 'en', false);
            $variantNameAr = $variant?->getTranslation('name', 'ar', false);

            $payload = [
                'product_name' => $productNameEn ?: ($productNameAr ?: $product->sku),
                'product_name_ar' => $productNameAr ?: null,
                'product_slug' => (string) $product->slug,
                'product_sku' => (string) $product->sku,
                'variant_name' => $variantNameEn ?: ($variantNameAr ?: null),
                'variant_name_ar' => $variantNameAr ?: null,
                'variant_sku' => $variant?->sku ?: null,
            ];

            if (! $dryRun) {
                $item->update($payload);
            }

            $updated++;
        }
    });

    $mode = $dryRun ? 'Dry run complete' : 'Backfill complete';
    $this->info("{$mode}: updated={$updated}, skipped_missing_product={$skipped}");

    return self::SUCCESS;
})->purpose('Backfill order item snapshot fields from products/variants');

Artisan::command('products:backfill-brand-snapshots {--chunk=500} {--dry-run} {--force}', function () {
    if (! Schema::hasColumns('products', ['brand_snapshot', 'category_snapshot'])) {
        $this->error('Snapshot columns do not exist on products. Run migrations first.');

        return self::FAILURE;
    }

    $chunkSize = max(1, (int) $this->option('chunk'));
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');
    $updated = 0;
    $skipped = 0;

    $query = Product::query()->with(['brand', 'categories'])->orderBy('id');

    if (! $force) {
        $query->whereNull('brand_snapshot');
    }

    $total = (clone $query)->count();
    $this->info("Processing {$total} products...");

    $query->chunkById($chunkSize, function ($products) use (&$updated, &$skipped, $dryRun) {
        foreach ($products as $product) {
            $brand = $product->brand;

            if (! $brand) {
                $skipped++;
                continue;
            }

            $nameEn = $brand->getTranslation('name', 'en', false);
            $nameAr = $brand->getTranslation('name', 'ar', false);

            $categorySnapshot = $product->categories
                ->map(function ($category) {
                    $nameEn = $category->getTranslation('name', 'en', false);
                    $nameAr = $category->getTranslation('name', 'ar', false);

                    return [
                        'id' => $category->id,
                        'name' => $nameEn ?: ($nameAr ?: null),
                        'name_ar' => $nameAr ?: null,
                    ];
                })
                ->values()
                ->all();

            $payload = [
                'brand_snapshot' => [
                    'id' => $brand->id,
                    'name' => $nameEn ?: ($nameAr ?: null),
                    'name_ar' => $nameAr ?: null,
                ],
                'category_snapshot' => $categorySnapshot,
            ];

            if (! $dryRun) {
                $product->update($payload);
            }

            $updated++;
        }
    });

    $mode = $dryRun ? 'Dry run complete' : 'Backfill complete';
    $this->info("{$mode}: updated={$updated}, skipped_missing_brand={$skipped}");

    return self::SUCCESS;
})->purpose('Backfill product brand/category snapshots');

Artisan::command('orders:backfill-header-snapshots {--chunk=500} {--dry-run} {--force}', function () {
    if (! Schema::hasColumns('orders', [
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address_snapshot',
    ])) {
        $this->error('Snapshot columns do not exist on orders. Run migrations first.');

        return self::FAILURE;
    }

    $chunkSize = max(1, (int) $this->option('chunk'));
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');
    $updated = 0;

    $query = \App\Models\Order::query()->with(['user', 'address', 'coupon'])->orderBy('id');

    if (! $force) {
        $query->where(function ($q) {
            $q->whereNull('customer_name')
                ->orWhereNull('shipping_address_snapshot');
        });
    }

    $total = (clone $query)->count();
    $this->info("Processing {$total} orders...");

    $query->chunkById($chunkSize, function ($orders) use (&$updated, $dryRun) {
        foreach ($orders as $order) {
            $address = $order->address;
            $user = $order->user;

            $payload = [
                'customer_name' => $user?->name ?? $order->customer_name,
                'customer_email' => $user?->email ?? $order->customer_email,
                'customer_phone' => $address?->phone ?? ($user?->phone ?? $order->customer_phone),
                'shipping_address_snapshot' => $address ? [
                    'name' => $address->name,
                    'phone' => $address->phone,
                    'address' => $address->address,
                    'city' => $address->city,
                    'state' => $address->state,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                ] : ($order->shipping_address_snapshot ?? null),
            ];

            if (! $dryRun) {
                $order->update($payload);
            }

            $updated++;
        }
    });

    $mode = $dryRun ? 'Dry run complete' : 'Backfill complete';
    $this->info("{$mode}: updated={$updated}");

    return self::SUCCESS;
})->purpose('Backfill order customer/address snapshots');

