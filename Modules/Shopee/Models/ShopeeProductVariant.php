<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeProductVariant extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(ShopeeProduct::class, 'shopee_product_id');
    }

    public function mapping()
    {
        return $this->hasOne(ShopeeProductMapping::class, 'shopee_model_id', 'model_id')
            ->whereColumn('shopee_item_id', 'shopee_products.item_id');
    }
}
