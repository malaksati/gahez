<?php

use App\V1\Http\Controllers\Api\AddressController;
use App\V1\Http\Controllers\Api\AuthController;
use App\V1\Http\Controllers\Api\BranchController;
use App\V1\Http\Controllers\Api\BrandController;
use App\V1\Http\Controllers\Api\CartItemController;
use App\V1\Http\Controllers\Api\CategoryController;
use App\V1\Http\Controllers\Api\GoalController;
use App\V1\Http\Controllers\Api\NotificationController;
use App\V1\Http\Controllers\Api\OfferController;
use App\V1\Http\Controllers\Api\OrderController;
use App\V1\Http\Controllers\Api\OrderRatingController;
use App\V1\Http\Controllers\Api\OrderRefundRequestController;
use App\V1\Http\Controllers\Api\ProductController;
use App\V1\Http\Controllers\Api\ProductRatingController;
use App\V1\Http\Controllers\Api\ProductReportController;
use App\V1\Http\Controllers\Api\ProfileController;
use App\V1\Http\Controllers\Api\ResetPasswordController;
use App\V1\Http\Controllers\Api\SliderController;
use App\V1\Http\Controllers\Api\StoreConfigController;
use App\V1\Http\Controllers\Api\SupportChatController;
use App\V1\Http\Controllers\Api\TicketController;
use App\V1\Http\Controllers\Api\WalletTransactionController;
use App\V1\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*

    API Routes for public users

*/

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/verify-email', [AuthController::class, 'verifyEmail'])->middleware('throttle:10,1');
Route::post('auth/verify-phone', [AuthController::class, 'verifyPhone'])->middleware('throttle:10,1');
Route::post('auth/resend-verification-code', [AuthController::class, 'resendVerificationCode'])->middleware('throttle:3,1');

Route::post('auth/reset-password/send-code', [ResetPasswordController::class, 'resetPasswordSendCode'])->middleware('throttle:5,1');
Route::post('auth/reset-password/verify-code', [ResetPasswordController::class, 'resetPasswordVerifyCode'])->middleware('throttle:10,1');
Route::post('auth/reset-password/set-new-password', [ResetPasswordController::class, 'resetPasswordSetNewPassword'])->middleware('throttle:10,1');

/*
    API Routes for authenticated users
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::match(['put', 'patch'], 'profile', [ProfileController::class, 'update']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
})->middleware('throttle:60,1');

Route::get('/user', fn (Request $request) => $request->user())->middleware('auth:sanctum')->middleware('throttle:60,1');

/*
    API Routes for public users without authentication
*/

Route::get('store/config', [StoreConfigController::class, 'show'])->middleware('throttle:60,1');

Route::middleware('throttle:30,1')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/tree', [CategoryController::class, 'tree']);
    Route::get('categories/{id}', [CategoryController::class, 'show'])->whereNumber('id');

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/slug/{slug}', [ProductController::class, 'showBySlug']);
    Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

    Route::get('brands', [BrandController::class, 'index']);
    Route::get('brands/{id}', [BrandController::class, 'show'])->whereNumber('id');

    Route::get('branches', [BranchController::class, 'index']);
    Route::get('branches/{id}', [BranchController::class, 'show'])->whereNumber('id');

    Route::get('sliders', [SliderController::class, 'index']);
    Route::get('offers', [OfferController::class, 'index']);

    /*
    API Routes for public users with authentication
    */

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('addresses', [AddressController::class, 'index']);
        Route::post('addresses', [AddressController::class, 'store']);
        Route::get('addresses/{id}', [AddressController::class, 'show'])->whereNumber('id');
        Route::put('addresses/{id}', [AddressController::class, 'update'])->whereNumber('id');
        Route::delete('addresses/{id}', [AddressController::class, 'destroy'])->whereNumber('id');

        Route::get('cart', [CartItemController::class, 'index']);
        Route::get('cart/checkout-preview', [CartItemController::class, 'checkoutPreview']);
        Route::post('cart/apply-coupon', [CartItemController::class, 'applyCoupon']);
        Route::put('cart/items/{cartItem}', [CartItemController::class, 'updateByCartItem'])->whereNumber('cartItem');
        Route::patch('cart/items/{cartItem}', [CartItemController::class, 'updateByCartItem'])->whereNumber('cartItem');
        Route::post('cart/{product}', [CartItemController::class, 'store'])->whereNumber('product');
        Route::put('cart/{product}', [CartItemController::class, 'update'])->whereNumber('product');
        Route::patch('cart/{product}', [CartItemController::class, 'update'])->whereNumber('product');
        Route::delete('cart/{product}', [CartItemController::class, 'destroy'])->whereNumber('product');
        Route::delete('cart', [CartItemController::class, 'clear']);

        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist/{product}', [WishlistController::class, 'toggle']);

        Route::get('wallet/history', [WalletTransactionController::class, 'index']);
        Route::get('goals', [GoalController::class, 'index']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->whereNumber('order');
        Route::post('orders/{order}/pay', [OrderController::class, 'pay'])->whereNumber('order');
        Route::post('orders/{order}/reorder', [OrderController::class, 'reorder'])->whereNumber('order');
        Route::post('orders/{order}/refund-request', [OrderRefundRequestController::class, 'store'])->whereNumber('order');
        Route::post('orders/{order}/rate', [OrderRatingController::class, 'store'])->whereNumber('order');

        Route::get('refund-requests', [OrderRefundRequestController::class, 'index']);

        Route::post('products/{product}/rate', [ProductRatingController::class, 'store']);
        Route::post('products/{product}/report', [ProductReportController::class, 'store']);

        Route::get('tickets', [TicketController::class, 'index']);
        Route::post('tickets', [TicketController::class, 'store']);
        Route::get('tickets/{id}', [TicketController::class, 'show'])->whereNumber('id');
        Route::put('tickets/{id}', [TicketController::class, 'update'])->whereNumber('id');
        Route::post('tickets/{id}/messages', [TicketController::class, 'storeMessage'])->whereNumber('id');

        Route::get('support-chats', [SupportChatController::class, 'index']);
        Route::post('support-chats', [SupportChatController::class, 'store']);
        Route::get('support-chats/{support}', [SupportChatController::class, 'show'])->whereNumber('support');
        Route::get('support-chats/{support}/messages', [SupportChatController::class, 'messages'])->whereNumber('support');
        Route::post('support-chats/{support}/messages', [SupportChatController::class, 'storeMessage'])->whereNumber('support');
    });
});
