<?php

namespace App\V1\Support;

class AdminSearchIndex
{
    /**
     * @return list<array{title: string, group: string, url: string, icon: string, keywords: string}>
     */
    public static function pages(): array
    {
        return [
            // Overview
            ['title' => __('messages.Dashboard'), 'group' => __('messages.Overview'), 'url' => route('v1.admin.dashboard'), 'icon' => 'speedometer2', 'keywords' => 'dashboard home overview'],

            // Catalog
            ['title' => __('messages.Categories'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.categories.index'), 'icon' => 'grid', 'keywords' => 'categories catalog sort order parent'],
            ['title' => __('messages.Import categories'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.categories.import'), 'icon' => 'upload', 'keywords' => 'import categories excel spreadsheet sort_order'],
            ['title' => __('messages.Export').' · '.__('messages.Categories'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.categories.export'), 'icon' => 'download', 'keywords' => 'export categories excel download'],
            ['title' => __('messages.Products'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.products.index'), 'icon' => 'box-seam', 'keywords' => 'products catalog items stock sort'],
            ['title' => __('messages.Import products'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.products.import'), 'icon' => 'upload', 'keywords' => 'import products excel spreadsheet sort_order is_in_stock'],
            ['title' => __('messages.Export').' · '.__('messages.Products'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.products.export'), 'icon' => 'download', 'keywords' => 'export products excel download'],
            ['title' => __('messages.Brands'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.brands.index'), 'icon' => 'award', 'keywords' => 'brands vendors'],
            ['title' => __('messages.Branches'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.branches.index'), 'icon' => 'shop', 'keywords' => 'branches stores'],
            ['title' => __('messages.Variants'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.variants.index'), 'icon' => 'tags', 'keywords' => 'variants attributes size color options'],
            ['title' => __('messages.Import variants'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.variants.import'), 'icon' => 'upload', 'keywords' => 'import variants excel'],
            ['title' => __('messages.Variant options'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.variant-options.index'), 'icon' => 'list-ul', 'keywords' => 'variant options values'],
            ['title' => __('messages.Import variant options'), 'group' => __('messages.Catalog'), 'url' => route('v1.admin.variant-options.import'), 'icon' => 'upload', 'keywords' => 'import variant options excel'],

            // Marketing
            ['title' => __('messages.Coupons'), 'group' => __('messages.Marketing'), 'url' => route('v1.admin.coupons.index'), 'icon' => 'ticket-perforated', 'keywords' => 'coupons discount codes promo'],
            ['title' => __('messages.Offers'), 'group' => __('messages.Marketing'), 'url' => route('v1.admin.offers.index'), 'icon' => 'gift', 'keywords' => 'offers promotions deals gifts'],
            ['title' => __('messages.Sliders'), 'group' => __('messages.Marketing'), 'url' => route('v1.admin.sliders.index'), 'icon' => 'images', 'keywords' => 'sliders banners homepage carousel'],

            // Sales
            ['title' => __('messages.Orders'), 'group' => __('messages.Sales'), 'url' => route('v1.admin.orders.index'), 'icon' => 'cart-check', 'keywords' => 'orders sales checkout payment status'],
            ['title' => __('messages.Create order'), 'group' => __('messages.Sales'), 'url' => route('v1.admin.orders.create'), 'icon' => 'plus-circle', 'keywords' => 'create order manual phone walk-in'],
            ['title' => __('messages.Order refund requests'), 'group' => __('messages.Sales'), 'url' => route('v1.admin.order-refund-requests.index'), 'icon' => 'arrow-return-left', 'keywords' => 'refund returns approve reject'],

            // Analytics
            ['title' => __('messages.Reports & Analytics'), 'group' => __('messages.Analytics'), 'url' => route('v1.admin.reports.index'), 'icon' => 'graph-up', 'keywords' => 'reports analytics statistics sales payment methods'],
            ['title' => __('messages.Earnings'), 'group' => __('messages.Analytics'), 'url' => route('v1.admin.reports.earnings'), 'icon' => 'cash-stack', 'keywords' => 'earnings revenue profit income'],
            ['title' => __('messages.Product performance'), 'group' => __('messages.Analytics'), 'url' => route('v1.admin.reports.product-performance'), 'icon' => 'bar-chart', 'keywords' => 'product performance bestsellers'],

            // Rating & Support
            ['title' => __('messages.Product ratings'), 'group' => __('messages.Rating & Support'), 'url' => route('v1.admin.product-ratings.index'), 'icon' => 'star', 'keywords' => 'ratings reviews stars visibility'],
            ['title' => __('messages.Product reports'), 'group' => __('messages.Rating & Support'), 'url' => route('v1.admin.product-reports.index'), 'icon' => 'flag', 'keywords' => 'reports abuse flagged products'],
            ['title' => __('messages.Tickets'), 'group' => __('messages.Rating & Support'), 'url' => route('v1.admin.tickets.index'), 'icon' => 'chat-dots', 'keywords' => 'tickets support customer messages attachments'],

            // Users
            ['title' => __('messages.Admin Users'), 'group' => __('messages.Admins and Customers'), 'url' => route('v1.admin.admin-users.index'), 'icon' => 'person-gear', 'keywords' => 'admin users staff permissions roles'],
            ['title' => __('messages.Customer Users'), 'group' => __('messages.Admins and Customers'), 'url' => route('v1.admin.customers.index'), 'icon' => 'people', 'keywords' => 'customers users clients accounts phone email'],

            // System
            ['title' => __('messages.Notifications'), 'group' => __('messages.System'), 'url' => route('v1.admin.notifications.index'), 'icon' => 'bell', 'keywords' => 'notifications alerts inbox'],
            ['title' => __('messages.Settings'), 'group' => __('messages.System'), 'url' => route('v1.admin.settings.index'), 'icon' => 'gear', 'keywords' => 'settings configuration app store currency'],
            ['title' => __('messages.Store theme'), 'group' => __('messages.System'), 'url' => route('v1.admin.theme.index'), 'icon' => 'palette', 'keywords' => 'theme colors branding mobile app appearance'],
            ['title' => __('messages.Security'), 'group' => __('messages.System'), 'url' => route('v1.admin.security.index'), 'icon' => 'shield-lock', 'keywords' => 'security password account'],
            ['title' => __('messages.Help'), 'group' => __('messages.System'), 'url' => route('v1.admin.help.index'), 'icon' => 'question-circle', 'keywords' => 'help guide catalog orders payments deliveries marketing refunds support how to'],
            ['title' => __('messages.Profile'), 'group' => __('messages.System'), 'url' => route('v1.admin.profile.edit'), 'icon' => 'person', 'keywords' => 'profile account my user'],
        ];
    }

    /**
     * @return list<array{title: string, group: string, url: string, icon: string, keywords: string, subtitle?: string}>
     */
    public static function filterPages(string $query): array
    {
        $needle = mb_strtolower(trim($query));

        if ($needle === '') {
            return [];
        }

        return array_values(array_filter(static::pages(), static function (array $item) use ($needle): bool {
            $haystack = mb_strtolower($item['title'].' '.$item['group'].' '.$item['keywords']);

            return str_contains($haystack, $needle);
        }));
    }
}
