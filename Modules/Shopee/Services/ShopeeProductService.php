<?php

namespace Modules\Shopee\Services;

use Modules\Shopee\Models\ShopeeProduct;
use Modules\Shopee\Models\ShopeeProductVariant;
use Modules\Shopee\Models\ShopeeShop;

class ShopeeProductService
{
    protected ShopeeApiService $api;

    public function __construct(ShopeeApiService $api)
    {
        $this->api = $api;
    }

    public function syncProducts(ShopeeShop $shop): array
    {
        $results = ['synced' => 0, 'errors' => 0, 'messages' => []];

        try {
            $client = $this->api->createClientForShop($shop);

            $offset = 0;
            $pageSize = 50;
            $more = true;

            while ($more) {
                $response = $client->Product->getItemList([
                    'offset' => $offset,
                    'page_size' => $pageSize,
                    'item_status' => 'NORMAL',
                ]);

                $items = $response['item'] ?? [];
                if (empty($items)) {
                    break;
                }

                $itemIds = array_column($items, 'item_id');
                $this->fetchAndSaveProductDetails($shop, $client, $itemIds, $results);

                $more = $response['has_next_page'] ?? false;
                $offset += $pageSize;
            }

            // Also sync UNLIST items
            $offset = 0;
            $more = true;
            while ($more) {
                $response = $client->Product->getItemList([
                    'offset' => $offset,
                    'page_size' => $pageSize,
                    'item_status' => 'UNLIST',
                ]);

                $items = $response['item'] ?? [];
                if (empty($items)) {
                    break;
                }

                $itemIds = array_column($items, 'item_id');
                $this->fetchAndSaveProductDetails($shop, $client, $itemIds, $results);

                $more = $response['has_next_page'] ?? false;
                $offset += $pageSize;
            }

            $this->api->log($shop, 'product_sync', 'success',
                "Synced {$results['synced']} products, {$results['errors']} errors");

        } catch (\Exception $e) {
            $results['errors']++;
            $results['messages'][] = $e->getMessage();
            $this->api->log($shop, 'product_sync', 'error', 'Product sync failed: ' . $e->getMessage());
        }

        return $results;
    }

    protected function fetchAndSaveProductDetails(ShopeeShop $shop, $client, array $itemIds, array &$results): void
    {
        $chunks = array_chunk($itemIds, 50);

        foreach ($chunks as $chunk) {
            try {
                $details = $client->Product->getItemBaseInfo($chunk);

                foreach ($details['item_list'] ?? [] as $itemData) {
                    $this->saveProduct($shop, $itemData);

                    // Fetch model info if product has models
                    if ($itemData['has_model'] ?? false) {
                        try {
                            $modelInfo = $client->Product->getModelList($itemData['item_id']);
                            $product = ShopeeProduct::where('shopee_shop_id', $shop->id)
                                ->where('item_id', $itemData['item_id'])
                                ->first();
                            if ($product && isset($modelInfo['model'])) {
                                $this->saveVariants($product, $modelInfo['model']);
                            }
                        } catch (\Exception $e) {
                            // Model fetch failed, skip
                        }
                    }

                    $results['synced']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                $results['messages'][] = "Batch error: " . $e->getMessage();
            }
        }
    }

    protected function saveProduct(ShopeeShop $shop, array $data): ShopeeProduct
    {
        $imageUrl = null;
        if (!empty($data['image']['image_url_list'])) {
            $imageUrl = $data['image']['image_url_list'][0];
        }

        $priceInfo = $data['price_info'] ?? [];
        $price = 0;
        if (!empty($priceInfo)) {
            $price = $priceInfo[0]['current_price'] ?? $priceInfo[0]['original_price'] ?? 0;
        }

        $stockInfo = $data['stock_info_v2'] ?? $data['stock_info'] ?? [];
        $stock = $stockInfo['current_stock'] ?? $stockInfo['normal_stock'] ?? 0;

        return ShopeeProduct::updateOrCreate(
            [
                'shopee_shop_id' => $shop->id,
                'item_id' => $data['item_id'],
            ],
            [
                'business_id' => $shop->business_id,
                'item_name' => $data['item_name'] ?? '',
                'item_sku' => $data['item_sku'] ?? null,
                'item_status' => $data['item_status'] ?? 'NORMAL',
                'image_url' => $imageUrl,
                'stock' => $stock,
                'price' => $price,
                'has_model' => $data['has_model'] ?? false,
            ]
        );
    }

    protected function saveVariants(ShopeeProduct $product, array $models): void
    {
        foreach ($models as $model) {
            $priceInfo = $model['price_info'] ?? [];
            $price = 0;
            if (!empty($priceInfo)) {
                $price = $priceInfo[0]['current_price'] ?? $priceInfo[0]['original_price'] ?? 0;
            }

            $stockInfo = $model['stock_info_v2'] ?? $model['stock_info'] ?? [];
            $stock = $stockInfo['current_stock'] ?? $stockInfo['normal_stock'] ?? 0;

            ShopeeProductVariant::updateOrCreate(
                [
                    'shopee_product_id' => $product->id,
                    'model_id' => $model['model_id'],
                ],
                [
                    'model_name' => $model['model_name'] ?? null,
                    'model_sku' => $model['model_sku'] ?? null,
                    'stock' => $stock,
                    'price' => $price,
                    'image_url' => $model['image_url'] ?? null,
                ]
            );
        }
    }
}
