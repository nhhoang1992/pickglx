<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeProductMapping extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'auto_destock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function shop()
    {
        return $this->belongsTo(ShopeeShop::class, 'shopee_shop_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class, 'variation_id');
    }

    public static function findByShopeeItem(int $shopId, int $itemId, ?string $modelId = null): ?self
    {
        $query = static::where('shopee_shop_id', $shopId)
            ->where('shopee_item_id', $itemId)
            ->where('is_active', true);

        if ($modelId) {
            $query->where('shopee_model_id', $modelId);
        } else {
            $query->whereNull('shopee_model_id');
        }

        return $query->first();
    }

    public static function findBySku(int $businessId, string $sku): ?self
    {
        return static::where('business_id', $businessId)
            ->where('shopee_sku', $sku)
            ->where('is_active', true)
            ->first();
    }
}
