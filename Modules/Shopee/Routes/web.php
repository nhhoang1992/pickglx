<?php

use Illuminate\Support\Facades\Route;
use Modules\Shopee\Http\Controllers\ShopeeAuthController;
use Modules\Shopee\Http\Controllers\ShopeeOrderController;
use Modules\Shopee\Http\Controllers\ShopeeProductMappingController;
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

        // Product mapping routes
        Route::get('/product-mappings', [ShopeeProductMappingController::class, 'index'])
            ->name('shopee.product-mappings');

        Route::get('/product-mappings/create', [ShopeeProductMappingController::class, 'create'])
            ->name('shopee.product-mappings.create');

        Route::post('/product-mappings', [ShopeeProductMappingController::class, 'store'])
            ->name('shopee.product-mappings.store');

        Route::post('/product-mappings/auto-match', [ShopeeProductMappingController::class, 'autoMatch'])
            ->name('shopee.product-mappings.auto-match');

        Route::get('/product-mappings/{id}/edit', [ShopeeProductMappingController::class, 'edit'])
            ->name('shopee.product-mappings.edit');

        Route::put('/product-mappings/{id}', [ShopeeProductMappingController::class, 'update'])
            ->name('shopee.product-mappings.update');

        Route::delete('/product-mappings/{id}', [ShopeeProductMappingController::class, 'destroy'])
            ->name('shopee.product-mappings.destroy');

        Route::get('/product-mappings/variations', [ShopeeProductMappingController::class, 'getVariations'])
            ->name('shopee.product-mappings.variations');
    });
