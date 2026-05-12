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

    const SHOPEE_STATUS_MAP = [
        'UNPAID' => 'new',
        'READY_TO_SHIP' => 'confirmed',
        'PROCESSED' => 'ready_to_ship',
        'SHIPPED' => 'shipped',
        'COMPLETED' => 'completed',
        'IN_CANCEL' => 'cancelled',
        'CANCELLED' => 'cancelled',
        'INVOICE_PENDING' => 'new',
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
