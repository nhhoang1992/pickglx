<?php

use Illuminate\Support\Facades\Route;
use Modules\Shopee\Http\Controllers\ShopeeAuthController;
use Modules\Shopee\Http\Controllers\ShopeeOrderController;
use Modules\Shopee\Http\Controllers\ShopeeProductMappingController;
use Modules\Shopee\Http\Controllers\ShopeeReportController;
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

        Route::post('/product-mappings/sync-products', [ShopeeProductMappingController::class, 'syncProducts'])
            ->name('shopee.product-mappings.sync-products');

        Route::post('/product-mappings/link', [ShopeeProductMappingController::class, 'linkProduct'])
            ->name('shopee.product-mappings.link');

        Route::post('/product-mappings/unlink', [ShopeeProductMappingController::class, 'unlinkProduct'])
            ->name('shopee.product-mappings.unlink');

        Route::post('/product-mappings/auto-match', [ShopeeProductMappingController::class, 'autoMatch'])
            ->name('shopee.product-mappings.auto-match');

        Route::get('/product-mappings/variations', [ShopeeProductMappingController::class, 'getVariations'])
            ->name('shopee.product-mappings.variations');

        Route::get('/product-mappings/search-variations', [ShopeeProductMappingController::class, 'searchVariations'])
            ->name('shopee.product-mappings.search-variations');

        // Report routes
        Route::get('/reports', [ShopeeReportController::class, 'dashboard'])
            ->name('shopee.reports.dashboard');

        Route::get('/reports/order-analysis', [ShopeeReportController::class, 'orderAnalysis'])
            ->name('shopee.reports.order-analysis');

        Route::get('/reports/top-products', [ShopeeReportController::class, 'topProducts'])
            ->name('shopee.reports.top-products');

        Route::get('/reports/shipping', [ShopeeReportController::class, 'shipping'])
            ->name('shopee.reports.shipping');

        Route::get('/reports/reconciliation', [ShopeeReportController::class, 'reconciliation'])
            ->name('shopee.reports.reconciliation');
    });
