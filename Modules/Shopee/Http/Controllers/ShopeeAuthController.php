<?php

namespace Modules\Shopee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shopee\Models\ShopeeConfig;
use Modules\Shopee\Models\ShopeeShop;
use Modules\Shopee\Services\ShopeeApiService;

class ShopeeAuthController extends Controller
{
    protected ShopeeApiService $shopeeApi;

    public function __construct(ShopeeApiService $shopeeApi)
    {
        $this->shopeeApi = $shopeeApi;
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        $shopId = $request->query('shop_id');

        if (!$code || !$shopId) {
            return redirect()->route('shopee.settings')
                ->with('status', [
                    'success' => false,
                    'msg' => __('shopee::lang.auth_failed_missing_params'),
                ]);
        }

        $businessId = session('business.id');
        $config = ShopeeConfig::where('business_id', $businessId)->first();

        if (!$config) {
            return redirect()->route('shopee.settings')
                ->with('status', [
                    'success' => false,
                    'msg' => __('shopee::lang.config_not_found'),
                ]);
        }

        try {
            $tokenData = $this->shopeeApi->getAccessToken($config, $code, (int) $shopId);

            $shop = ShopeeShop::updateOrCreate(
                [
                    'shopee_config_id' => $config->id,
                    'shop_id' => $shopId,
                ],
                [
                    'business_id' => $businessId,
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'token_expires_at' => now()->addSeconds($tokenData['expire_in']),
                    'status' => 'connected',
                ]
            );

            // Fetch and store shop info
            $this->shopeeApi->getShopInfo($shop);

            $this->shopeeApi->log($shop, 'auth', 'success', 'Shop connected successfully');

            return redirect()->route('shopee.settings')
                ->with('status', [
                    'success' => true,
                    'msg' => __('shopee::lang.shop_connected_successfully', [
                        'shop' => $shop->shop_name ?? $shopId,
                    ]),
                ]);
        } catch (\Exception $e) {
            return redirect()->route('shopee.settings')
                ->with('status', [
                    'success' => false,
                    'msg' => __('shopee::lang.auth_failed') . ': ' . $e->getMessage(),
                ]);
        }
    }
}
