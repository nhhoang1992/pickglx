<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeSyncLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopeeShop::class, 'shopee_shop_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
