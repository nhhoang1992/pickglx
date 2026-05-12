<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeProduct extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'has_model' => 'boolean',
        'price' => 'decimal:2',
        'category_id' => 'array',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopeeShop::class, 'shopee_shop_id');
    }

    public function variants()
    {
        return $this->hasMany(ShopeeProductVariant::class, 'shopee_product_id');
    }

    public function mappings()
    {
        return $this->hasMany(ShopeeProductMapping::class, 'shopee_item_id', 'item_id')
            ->where('shopee_shop_id', $this->shopee_shop_id);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'NORMAL' => 'Đang bán',
            'BANNED' => 'Bị cấm',
            'DELETED' => 'Đã xóa',
            'UNLIST' => 'Ẩn',
        ];
        return $labels[$this->item_status] ?? $this->item_status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'NORMAL' => '#4CAF50',
            'BANNED' => '#f44336',
            'DELETED' => '#9E9E9E',
            'UNLIST' => '#FF9800',
        ];
        return $colors[$this->item_status] ?? '#666';
    }
}
