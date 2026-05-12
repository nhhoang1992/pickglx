<?php

namespace Modules\Shopee\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeShop extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'shop_info' => 'array',
        'token_expires_at' => 'datetime',
    ];

    public function config()
    {
        return $this->belongsTo(ShopeeConfig::class, 'shopee_config_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(ShopeeSyncLog::class);
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }

        $buffer = config('shopee.token_refresh_buffer', 300);
        return $this->token_expires_at->subSeconds($buffer)->isPast();
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected' && !$this->isTokenExpired();
    }
}
