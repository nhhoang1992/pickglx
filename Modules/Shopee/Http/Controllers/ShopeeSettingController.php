<?php

namespace Modules\Shopee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shopee\Models\ShopeeConfig;
use Modules\Shopee\Models\ShopeeShop;
use Modules\Shopee\Models\ShopeeSyncLog;
use Modules\Shopee\Services\ShopeeApiService;

class ShopeeSettingController extends Controller
{
    protected ShopeeApiService $shopeeApi;

    public function __construct(ShopeeApiService $shopeeApi)
    {
        $this->shopeeApi = $shopeeApi;
    }

    public function index()
    {
        $businessId = session('business.id');

        $config = ShopeeConfig::where('business_id', $businessId)->first();
        $shops = $config
            ? ShopeeShop::where('shopee_config_id', $config->id)->get()
            : collect();

        $recentLogs = ShopeeSyncLog::where('business_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('shopee::shopee.settings', compact('config', 'shops', 'recentLogs'));
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|string',
            'partner_key' => 'required|string',
            'sandbox_mode' => 'nullable|boolean',
        ]);

        $businessId = session('business.id');

        $config = ShopeeConfig::updateOrCreate(
            ['business_id' => $businessId],
            [
                'partner_id' => $request->partner_id,
                'partner_key' => $request->partner_key,
                'sandbox_mode' => $request->boolean('sandbox_mode', true),
                'is_active' => true,
            ]
        );

        return redirect()->route('shopee.settings')
            ->with('status', [
                'success' => true,
                'msg' => __('shopee::lang.config_saved_successfully'),
            ]);
    }

    public function connectShop()
    {
        $businessId = session('business.id');
        $config = ShopeeConfig::where('business_id', $businessId)->first();

        if (!$config) {
            return redirect()->route('shopee.settings')
                ->with('status', [
                    'success' => false,
                    'msg' => __('shopee::lang.please_save_config_first'),
                ]);
        }

        $redirectUrl = route('shopee.auth.callback');
        $authUrl = $this->shopeeApi->getAuthUrl($config, $redirectUrl);

        return redirect($authUrl);
    }

    public function disconnectShop($id)
    {
        $businessId = session('business.id');
        $shop = ShopeeShop::where('id', $id)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $shop->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ]);

        return redirect()->route('shopee.settings')
            ->with('status', [
                'success' => true,
                'msg' => __('shopee::lang.shop_disconnected'),
            ]);
    }

    public function refreshShopToken($id)
    {
        $businessId = session('business.id');
        $shop = ShopeeShop::where('id', $id)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $result = $this->shopeeApi->refreshToken($shop);

        if ($result) {
            $this->shopeeApi->getShopInfo($shop);
        }

        return redirect()->route('shopee.settings')
            ->with('status', [
                'success' => $result,
                'msg' => $result
                    ? __('shopee::lang.token_refreshed')
                    : __('shopee::lang.token_refresh_failed'),
            ]);
    }

    public function syncLogs(Request $request)
    {
        $businessId = session('business.id');

        $logs = ShopeeSyncLog::where('business_id', $businessId)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('shopee::shopee.sync_logs', compact('logs'));
    }
}
