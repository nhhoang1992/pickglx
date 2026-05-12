<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'raw_data' => 'array',
        'is_express' => 'boolean',
        'total_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'buyer_paid_amount' => 'decimal:2',
        'ship_by_date' => 'datetime',
        'order_created_at' => 'datetime',
        'order_paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'stock_deducted' => 'boolean',
        'stock_deducted_at' => 'datetime',
    ];

    const INTERNAL_STATUSES = [
        'new' => 'Mới',
        'confirmed' => 'Đã xác nhận',
        'packing' => 'Đang đóng gói',
        'ready_to_ship' => 'Chờ gửi hàng',
        'shipped' => 'Đã gửi hàng',
        'delivering' => 'Đang giao',
        'completed' => 'Thành công',
        'returned' => 'Hoàn',
        'cancelled' => 'Hủy',
    ];

    // Shopee API v2 order_status → internal_status mapping
    // UNPAID: buyer placed order but hasn't paid
    // READY_TO_SHIP: buyer paid, seller needs to arrange shipment
    // PROCESSED: seller arranged shipment, waiting for courier pickup
    // SHIPPED: courier picked up package, in transit
    // TO_CONFIRM_RECEIVE: delivered, waiting buyer confirmation
    // COMPLETED: buyer confirmed receipt / auto-completed
    // IN_CANCEL / CANCELLED: order cancelled
    // INVOICE_PENDING: waiting for invoice (some regions)
    // RETRY_SHIP: shipment failed, need to retry
    // TO_RETURN: buyer requested return
    const SHOPEE_STATUS_MAP = [
        'UNPAID' => 'new',
        'READY_TO_SHIP' => 'new',
        'PROCESSED' => 'ready_to_ship',
        'SHIPPED' => 'shipped',
        'TO_CONFIRM_RECEIVE' => 'delivering',
        'COMPLETED' => 'completed',
        'IN_CANCEL' => 'cancelled',
        'CANCELLED' => 'cancelled',
        'INVOICE_PENDING' => 'new',
        'RETRY_SHIP' => 'ready_to_ship',
        'TO_RETURN' => 'returned',
    ];

    public function items()
    {
        return $this->hasMany(ShopeeOrderItem::class);
    }

    public function shop()
    {
        return $this->belongsTo(ShopeeShop::class, 'shopee_shop_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(\App\User::class, 'confirmed_by');
    }

    public function packedBy()
    {
        return $this->belongsTo(\App\User::class, 'packed_by');
    }

    public function shippedBy()
    {
        return $this->belongsTo(\App\User::class, 'shipped_by');
    }

    public function getInternalStatusLabelAttribute(): string
    {
        return self::INTERNAL_STATUSES[$this->internal_status] ?? $this->internal_status;
    }

    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function scopeByInternalStatus($query, string $status)
    {
        return $query->where('internal_status', $status);
    }

    public function scopeByShop($query, int $shopId)
    {
        return $query->where('shopee_shop_id', $shopId);
    }

    public function scopeExpress($query)
    {
        return $query->where('is_express', true);
    }
}
