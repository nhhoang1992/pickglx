<?php

use Illuminate\Support\Facades\Route;
use Modules\Shopee\Http\Controllers\ShopeeAuthController;
use Modules\Shopee\Http\Controllers\ShopeeOrderController;
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

        // Order management routes
        Route::get('/orders', [ShopeeOrderController::class, 'index'])
            ->name('shopee.orders');

        Route::get('/orders/{id}', [ShopeeOrderController::class, 'show'])
            ->name('shopee.orders.show');

        Route::post('/orders/sync', [ShopeeOrderController::class, 'syncOrders'])
            ->name('shopee.orders.sync');

        Route::post('/orders/{id}/confirm', [ShopeeOrderController::class, 'confirm'])
            ->name('shopee.orders.confirm');

        Route::post('/orders/{id}/packing', [ShopeeOrderController::class, 'markPacking'])
            ->name('shopee.orders.packing');

        Route::post('/orders/{id}/ready-to-ship', [ShopeeOrderController::class, 'markReadyToShip'])
            ->name('shopee.orders.ready-to-ship');

        Route::post('/orders/{id}/ship', [ShopeeOrderController::class, 'ship'])
            ->name('shopee.orders.ship');

        Route::post('/orders/bulk-action', [ShopeeOrderController::class, 'bulkAction'])
            ->name('shopee.orders.bulk-action');

        Route::post('/orders/{id}/note', [ShopeeOrderController::class, 'updateNote'])
            ->name('shopee.orders.note');
    });
