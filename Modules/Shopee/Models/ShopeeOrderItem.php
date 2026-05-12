<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeOrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'original_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'weight' => 'decimal:4',
        'is_wholesale' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(ShopeeOrder::class, 'shopee_order_id');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->discounted_price * $this->quantity;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->item_name;
        if ($this->model_name) {
            $name .= ' - ' . $this->model_name;
        }
        return $name;
    }
}
