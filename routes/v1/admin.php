<?php

use App\V1\Http\Controllers\Web\Admin\AdminSearchController;
use App\V1\Http\Controllers\Web\Admin\AdminUserController;
use App\V1\Http\Controllers\Web\Admin\BranchController;
use App\V1\Http\Controllers\Web\Admin\BrandController;
use App\V1\Http\Controllers\Web\Admin\CategoryController;
use App\V1\Http\Controllers\Web\Admin\CategoryDataTransferController;
use App\V1\Http\Controllers\Web\Admin\CouponController;
use App\V1\Http\Controllers\Web\Admin\CustomerController;
use App\V1\Http\Controllers\Web\Admin\DashboardController;
use App\V1\Http\Controllers\Web\Admin\DataTransferBatchController;
use App\V1\Http\Controllers\Web\Admin\HelpController;
use App\V1\Http\Controllers\Web\Admin\NotificationController;
use App\V1\Http\Controllers\Web\Admin\GoalController;
use App\V1\Http\Controllers\Web\Admin\OfferController;
use App\V1\Http\Controllers\Web\Admin\OrderController;
use App\V1\Http\Controllers\Web\Admin\OrderRefundRequestController;
use App\V1\Http\Controllers\Web\Admin\ProductController;
use App\V1\Http\Controllers\Web\Admin\ProductDataTransferController;
use App\V1\Http\Controllers\Web\Admin\ProductRatingController;
use App\V1\Http\Controllers\Web\Admin\ProductReportController;
use App\V1\Http\Controllers\Web\Admin\ProfileController;
use App\V1\Http\Controllers\Web\Admin\ReportController;
use App\V1\Http\Controllers\Web\Admin\SecurityController;
use App\V1\Http\Controllers\Web\Admin\SettingsController;
use App\V1\Http\Controllers\Web\Admin\SliderController;
use App\V1\Http\Controllers\Web\Admin\SupportChatController;
use App\V1\Http\Controllers\Web\Admin\TicketController;
use App\V1\Http\Controllers\Web\Admin\VariantController;
use App\V1\Http\Controllers\Web\Admin\VariantDataTransferController;
use App\V1\Http\Controllers\Web\Admin\VariantOptionController;
use App\V1\Http\Controllers\Web\Admin\VariantOptionDataTransferController;
use Illuminate\Support\Facades\Route;

// Dashboard & global
Route::middleware('permission:view dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('search', AdminSearchController::class)->name('search');
});

// Notifications (available to all admins)
Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
Route::get('data-transfer/batches/{batch}/status', [DataTransferBatchController::class, 'status'])->name('data-transfer.batches.status');

// Profile (available to all admins)
Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

// Categories
Route::middleware('permission:manage categories')->group(function () {
    Route::get('categories/export', [CategoryDataTransferController::class, 'export'])->name('categories.export');
    Route::get('categories/import', [CategoryDataTransferController::class, 'create'])->name('categories.import');
    Route::post('categories/import', [CategoryDataTransferController::class, 'store'])->name('categories.import.store');
    Route::get('categories/import/template', [CategoryDataTransferController::class, 'template'])->name('categories.import.template');
    Route::get('categories/import-export/{batch}', [CategoryDataTransferController::class, 'show'])->name('categories.import-export.show');
    Route::get('categories/import-export/{batch}/download', [CategoryDataTransferController::class, 'download'])->name('categories.import-export.download');
    Route::resource('categories', CategoryController::class);
});

// Products
Route::middleware('permission:manage products')->group(function () {
    Route::get('products/export', [ProductDataTransferController::class, 'export'])->name('products.export');
    Route::get('products/import', [ProductDataTransferController::class, 'create'])->name('products.import');
    Route::post('products/import', [ProductDataTransferController::class, 'store'])->name('products.import.store');
    Route::get('products/import/template', [ProductDataTransferController::class, 'template'])->name('products.import.template');
    Route::get('products/import-export/{batch}', [ProductDataTransferController::class, 'show'])->name('products.import-export.show');
    Route::get('products/import-export/{batch}/download', [ProductDataTransferController::class, 'download'])->name('products.import-export.download');
    Route::get('products/next-sku', [ProductController::class, 'nextSku'])->name('products.next-sku');
    Route::post('products/quick-catalog-variant', [ProductController::class, 'quickStoreCatalogVariant'])->name('products.quick-catalog-variant');
    Route::post('products/quick-variant-option', [ProductController::class, 'quickStoreVariantOption'])->name('products.quick-variant-option');
    Route::post('products/quick-catalog-unit', [ProductController::class, 'quickStoreCatalogUnit'])->name('products.quick-catalog-unit');
    Route::post('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::post('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::post('products/{product}/toggle-approved', [ProductController::class, 'toggleApproved'])->name('products.toggle-approved');
    Route::resource('products', ProductController::class);
});

// Brands
Route::middleware('permission:manage brands')->group(function () {
    Route::resource('brands', BrandController::class);
});

// Branches
Route::middleware('permission:manage branches')->group(function () {
    Route::resource('branches', BranchController::class);
});

// Variants
Route::middleware('permission:manage variants')->group(function () {
    Route::get('variants/export', [VariantDataTransferController::class, 'export'])->name('variants.export');
    Route::get('variants/import', [VariantDataTransferController::class, 'create'])->name('variants.import');
    Route::post('variants/import', [VariantDataTransferController::class, 'store'])->name('variants.import.store');
    Route::get('variants/import/template', [VariantDataTransferController::class, 'template'])->name('variants.import.template');
    Route::get('variants/import-export/{batch}', [VariantDataTransferController::class, 'show'])->name('variants.import-export.show');
    Route::get('variants/import-export/{batch}/download', [VariantDataTransferController::class, 'download'])->name('variants.import-export.download');
    Route::resource('variants', VariantController::class);

    Route::get('variant-options/export', [VariantOptionDataTransferController::class, 'export'])->name('variant-options.export');
    Route::get('variant-options/import', [VariantOptionDataTransferController::class, 'create'])->name('variant-options.import');
    Route::post('variant-options/import', [VariantOptionDataTransferController::class, 'store'])->name('variant-options.import.store');
    Route::get('variant-options/import/template', [VariantOptionDataTransferController::class, 'template'])->name('variant-options.import.template');
    Route::get('variant-options/import-export/{batch}', [VariantOptionDataTransferController::class, 'show'])->name('variant-options.import-export.show');
    Route::get('variant-options/import-export/{batch}/download', [VariantOptionDataTransferController::class, 'download'])->name('variant-options.import-export.download');
    Route::resource('variant-options', VariantOptionController::class);
});

// Coupons
Route::middleware('permission:manage coupons')->group(function () {
    Route::post('coupons/{coupon}/notify-customers', [CouponController::class, 'notifyCustomers'])->name('coupons.notify-customers');
    Route::resource('coupons', CouponController::class);
});

// Offers
Route::middleware('permission:manage offers')->group(function () {
    Route::post('offers/{offer}/notify-customers', [OfferController::class, 'notifyCustomers'])->name('offers.notify-customers');
    Route::resource('offers', OfferController::class);
});

// Goals
Route::middleware('permission:manage goals')->group(function () {
    Route::post('goals/{goal}/toggle-active', [GoalController::class, 'toggleActive'])->name('goals.toggle-active');
    Route::resource('goals', GoalController::class);
});

// Sliders
Route::middleware('permission:manage sliders')->group(function () {
    Route::resource('sliders', SliderController::class);
});

// Orders
Route::middleware('permission:manage orders')->group(function () {
    Route::resource('orders', OrderController::class)->except(['destroy']);
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::put('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
});

// Refund Requests
Route::middleware('permission:manage refunds')->group(function () {
    Route::post('order-refund-requests/{order_refund_request}/approve', [OrderRefundRequestController::class, 'approve'])->name('order-refund-requests.approve');
    Route::post('order-refund-requests/{order_refund_request}/reject', [OrderRefundRequestController::class, 'reject'])->name('order-refund-requests.reject');
    Route::resource('order-refund-requests', OrderRefundRequestController::class)->only(['index', 'show', 'edit', 'update']);
});

// Reports & Analytics
Route::middleware('permission:view reports')->group(function () {
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/earnings', [ReportController::class, 'earnings'])->name('reports.earnings');
    Route::get('reports/product-performance', [ReportController::class, 'productPerformance'])->name('reports.product-performance');
    Route::get('reports/{type}/export', [ReportController::class, 'export'])
        ->name('reports.export')
        ->where('type', 'customers|sales-period|sales-payment-methods|top-products-categories|stock');
    Route::get('reports/{type}/export-pdf', [ReportController::class, 'exportPdf'])
        ->name('reports.export-pdf')
        ->where('type', 'customers|sales-period|sales-payment-methods|top-products-categories|stock');
    Route::get('reports/{type}', [ReportController::class, 'show'])
        ->name('reports.show')
        ->where('type', 'customers|sales-period|sales-payment-methods|top-products-categories|stock');
});

// Product Ratings
Route::middleware('permission:manage ratings')->group(function () {
    Route::get('product-ratings', [ProductRatingController::class, 'index'])->name('product-ratings.index');
    Route::post('product-ratings/{product_rating}/toggle-visibility', [ProductRatingController::class, 'toggleVisibility'])->name('product-ratings.toggle-visibility');
});

// Product Reports
Route::middleware('permission:manage product-reports')->group(function () {
    Route::get('product-reports', [ProductReportController::class, 'index'])->name('product-reports.index');
    Route::post('product-reports/{product_report}/status/{status}', [ProductReportController::class, 'updateStatus'])->name('product-reports.update-status');
});

// Tickets
Route::middleware('permission:manage tickets')->group(function () {
    Route::resource('tickets', TicketController::class)->only(['index', 'show', 'edit', 'update']);
    Route::post('tickets/{ticket}/messages', [TicketController::class, 'storeMessage'])->name('tickets.messages.store');
    Route::put('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
});

// Support chats
Route::middleware('permission:manage support-chats')->group(function () {
    Route::get('support-chats', [SupportChatController::class, 'index'])->name('support-chats.index');
    Route::get('support-chats/{support}', [SupportChatController::class, 'show'])->name('support-chats.show');
    Route::get('support-chats/{support}/messages', [SupportChatController::class, 'messages'])->name('support-chats.messages.index');
    Route::post('support-chats/{support}/messages', [SupportChatController::class, 'storeMessage'])->name('support-chats.messages.store');
    Route::put('support-chats/{support}/assign', [SupportChatController::class, 'assign'])->name('support-chats.assign');
    Route::put('support-chats/{support}/status', [SupportChatController::class, 'updateStatus'])->name('support-chats.status.update');
});

// Settings
Route::middleware('permission:manage settings')->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('security', [SecurityController::class, 'index'])->name('security.index');
    Route::get('help', [HelpController::class, 'index'])->name('help.index');
});

// Admin User Management
Route::middleware('permission:manage admins')->group(function () {
    Route::resource('admin-users', AdminUserController::class);
});

// Customer User Management
Route::middleware('permission:manage customers')->group(function () {
    Route::resource('customers', CustomerController::class);
});
