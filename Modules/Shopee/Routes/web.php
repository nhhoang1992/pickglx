<?php

use Illuminate\Support\Facades\Route;
use Modules\Shopee\Http\Controllers\ShopeeAuthController;
use Modules\Shopee\Http\Controllers\ShopeeSettingController;

Route::middleware(['web', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])
    ->prefix('shopee')
    ->group(function () {
        Route::get('/settings', [ShopeeSettingController::class, 'index'])
            ->name('shopee.settings');

        Route::post('/settings/save', [ShopeeSettingController::class, 'saveConfig'])
            ->name('shopee.settings.save');

        Route::get('/connect', [ShopeeSettingController::class, 'connectShop'])
            ->name('shopee.connect');

        Route::post('/disconnect/{id}', [ShopeeSettingController::class, 'disconnectShop'])
            ->name('shopee.disconnect');

        Route::post('/refresh-token/{id}', [ShopeeSettingController::class, 'refreshShopToken'])
            ->name('shopee.refresh-token');

        Route::get('/auth/callback', [ShopeeAuthController::class, 'callback'])
            ->name('shopee.auth.callback');

        Route::get('/sync-logs', [ShopeeSettingController::class, 'syncLogs'])
            ->name('shopee.sync-logs');
    });
