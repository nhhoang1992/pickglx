<?php

namespace Modules\Shopee\Providers;

use Illuminate\Support\ServiceProvider;

class ShopeeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'shopee');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function register()
    {
        $this->app->singleton(\Modules\Shopee\Services\ShopeeApiService::class);
    }
}
