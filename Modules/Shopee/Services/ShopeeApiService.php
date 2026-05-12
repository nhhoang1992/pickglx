<?php

namespace Modules\Shopee\Services;

use EcomPHP\Shopee\Client;
use Modules\Shopee\Models\ShopeeConfig;
use Modules\Shopee\Models\ShopeeShop;
use Modules\Shopee\Models\ShopeeSyncLog;

class ShopeeApiService
{
    protected ?Client $client = null;

    public function createClient(ShopeeConfig $config): Client
    {
        $client = new Client($config->partner_id, $config->partner_key);

        if ($config->sandbox_mode) {
            $client->useDebugMode();
        }

        $this->client = $client;
        return $client;
    }

    public function createClientForShop(ShopeeShop $shop): Client
    {
        $config = $shop->config;
        $client = $this->createClient($config);

        if ($shop->isTokenExpired() && $shop->refresh_token) {
            $this->refreshToken($shop);
            $shop->refresh();
        }

        if ($shop->access_token) {
            $client->setAccessToken($shop->shop_id, $shop->access_token);
        }

        return $client;
    }

    public function getAuthUrl(ShopeeConfig $config, string $redirectUrl): string
    {
        $client = $this->createClient($config);
        $auth = $client->auth();
        return $auth->createAuthRequest($redirectUrl, true);
    }

    public function getAccessToken(ShopeeConfig $config, string $code, int $shopId): array
    {
        $client = $this->createClient($config);
        $auth = $client->auth();
        $token = $auth->getToken($code, $shopId);

        return [
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expire_in' => $token['expire_in'] ?? 14400,
            'shop_id' => $shopId,
        ];
    }

    public function refreshToken(ShopeeShop $shop): bool
    {
        try {
            $client = $this->createClient($shop->config);
            $auth = $client->auth();
            $token = $auth->refreshNewToken($shop->refresh_token, $shop->shop_id);

            $shop->update([
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
                'token_expires_at' => now()->addSeconds($token['expire_in'] ?? 14400),
                'status' => 'connected',
            ]);

            $this->log($shop, 'auth', 'success', 'Token refreshed successfully');
            return true;
        } catch (\Exception $e) {
            $shop->update(['status' => 'expired']);
            $this->log($shop, 'auth', 'error', 'Token refresh failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getShopInfo(ShopeeShop $shop): ?array
    {
        try {
            $client = $this->createClientForShop($shop);
            $info = $client->Shop->getShopInfo();

            $shop->update([
                'shop_name' => $info['shop_name'] ?? $shop->shop_name,
                'shop_info' => $info,
            ]);

            $this->log($shop, 'other', 'success', 'Shop info retrieved');
            return $info;
        } catch (\Exception $e) {
            $this->log($shop, 'other', 'error', 'Get shop info failed: ' . $e->getMessage());
            return null;
        }
    }

    public function log(
        ShopeeShop $shop,
        string $type,
        string $status,
        string $message,
        ?array $requestData = null,
        ?array $responseData = null
    ): ShopeeSyncLog {
        return ShopeeSyncLog::create([
            'shopee_shop_id' => $shop->id,
            'business_id' => $shop->business_id,
            'type' => $type,
            'status' => $status,
            'message' => $message,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'created_by' => auth()->id(),
        ]);
    }
}
