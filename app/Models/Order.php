<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'sub_total',
        'order_discount',
        'coupon_id',
        'coupon_discount',
        'total_shipping',
        'points_discount',
        'total',
        'wallet_used',
        'status',
        'payment_status',
        'payment_method',
        'notes',
        'address_id',
        'shipping_address_snapshot',
        'total_commission',
        'refund_status',
        'refunded_total',
        'paid_at',
        'stock_deducted_at',
        'cashback_awarded_at',
        'cancellation_reason',
        'gift_offer_id',
        'gift_product_id',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'order_discount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'total_shipping' => 'decimal:2',
        'points_discount' => 'decimal:2',
        'total' => 'decimal:2',
        'wallet_used' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'refunded_total' => 'decimal:2',
        'paid_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'cashback_awarded_at' => 'datetime',
        'shipping_address_snapshot' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class)->latest();
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(OrderRefundRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderRating()
    {
        return $this->hasOne(OrderRating::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeReadyForDelivery($query)
    {
        return $query->where('status', 'ready_for_delivery');
    }
    
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaymentPaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePaymentFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    public function scopePaymentRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }
}
