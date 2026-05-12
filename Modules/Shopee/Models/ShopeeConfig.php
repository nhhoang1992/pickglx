<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeConfig extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'sandbox_mode' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['partner_key'];

    public function shops()
    {
        return $this->hasMany(ShopeeShop::class);
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }
}
