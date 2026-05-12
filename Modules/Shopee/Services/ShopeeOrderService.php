<?php

namespace Modules\Shopee\Services;

use App\Utils\ProductUtil;
use Modules\Shopee\Models\ShopeeOrder;
use Modules\Shopee\Models\ShopeeOrderItem;
use Modules\Shopee\Models\ShopeeProductMapping;
use Modules\Shopee\Models\ShopeeShop;

class ShopeeOrderService
{
    protected ShopeeApiService $api;

    public function __construct(ShopeeApiService $api)
    {
        $this->api = $api;
    }

    public function syncOrders(ShopeeShop $shop, array $options = []): array
    {
        $results = ['synced' => 0, 'errors' => 0, 'messages' => []];

        try {
            $client = $this->api->createClientForShop($shop);

            $timeFrom = $options['time_from'] ?? now()->subDays(7)->timestamp;
            $timeTo = $options['time_to'] ?? now()->timestamp;
            $status = $options['status'] ?? 'READY_TO_SHIP';

            $cursor = '';
            $more = true;

            while ($more) {
                $params = [
                    'time_range_field' => 'create_time',
                    'time_from' => $timeFrom,
                    'time_to' => $timeTo,
                    'page_size' => 50,
                    'order_status' => $status,
                ];
                if ($cursor) {
                    $params['cursor'] = $cursor;
                }

                $response = $client->Order->getOrderList($params);

                if (!isset($response['order_list'])) {
                    break;
                }

                $orderSns = array_column($response['order_list'], 'order_sn');

                if (!empty($orderSns)) {
                    $this->fetchAndSaveOrderDetails($shop, $client, $orderSns, $results);
                }

                $more = $response['more'] ?? false;
                $cursor = $response['next_cursor'] ?? '';
            }

            $this->api->log($shop, 'order_sync', 'success',
                "Synced {$results['synced']} orders, {$results['errors']} errors");

        } catch (\Exception $e) {
            $results['errors']++;
            $results['messages'][] = $e->getMessage();
            $this->api->log($shop, 'order_sync', 'error', 'Sync failed: ' . $e->getMessage());
        }

        return $results;
    }

    protected function fetchAndSaveOrderDetails(ShopeeShop $shop, $client, array $orderSns, array &$results): void
    {
        $chunks = array_chunk($orderSns, 50);

        foreach ($chunks as $chunk) {
            try {
                $details = $client->Order->getOrderDetail($chunk, [
                    'response_optional_fields' => 'buyer_user_id,buyer_username,estimated_shipping_fee,recipient_address,actual_shipping_fee,goods_to_declare,note,note_update_time,item_list,pay_time,dropshipper,credit_card_number,dropshipper_phone,split_up,buyer_cancel_reason,cancel_by,cancel_reason,actual_shipping_fee_confirmed,buyer_cpf_id,fulfillment_flag,pickup_done_time,package_list,shipping_carrier,payment_method,total_amount,invoice_data,checkout_shipping_carrier,reverse_shipping_fee,order_chargeable_weight_gram',
                ]);

                foreach ($details['order_list'] ?? [] as $orderData) {
                    $this->saveOrder($shop, $orderData);
                    $results['synced']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                $results['messages'][] = "Batch error: " . $e->getMessage();
            }
        }
    }

    public function saveOrder(ShopeeShop $shop, array $data): ShopeeOrder
    {
        $address = $data['recipient_address'] ?? [];

        $order = ShopeeOrder::updateOrCreate(
            [
                'shopee_shop_id' => $shop->id,
                'order_sn' => $data['order_sn'],
            ],
            [
                'business_id' => $shop->business_id,
                'order_status' => $data['order_status'],
                'internal_status' => $this->mapShopeeStatus($data['order_status']),
                'shipping_carrier' => $data['shipping_carrier'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'buyer_username' => $data['buyer_username'] ?? null,
                'buyer_name' => $address['name'] ?? null,
                'buyer_phone' => $address['phone'] ?? null,
                'shipping_address' => $address['full_address'] ?? null,
                'shipping_city' => $address['city'] ?? null,
                'shipping_district' => $address['district'] ?? null,
                'shipping_ward' => $address['town'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'total_amount' => $data['total_amount'] ?? 0,
                'shipping_fee' => $data['actual_shipping_fee'] ?? $data['estimated_shipping_fee'] ?? 0,
                'estimated_shipping_fee' => $data['estimated_shipping_fee'] ?? 0,
                'buyer_paid_amount' => $data['buyer_total_amount'] ?? $data['total_amount'] ?? 0,
                'currency' => $data['currency'] ?? 'VND',
                'days_to_ship' => $data['days_to_ship'] ?? null,
                'ship_by_date' => isset($data['ship_by_date']) ? date('Y-m-d H:i:s', $data['ship_by_date']) : null,
                'is_express' => ($data['checkout_shipping_carrier'] ?? '') === 'Hỏa Tốc'
                    || str_contains($data['shipping_carrier'] ?? '', 'Express'),
                'message_to_seller' => $data['note'] ?? null,
                'order_created_at' => isset($data['create_time']) ? date('Y-m-d H:i:s', $data['create_time']) : null,
                'order_paid_at' => isset($data['pay_time']) ? date('Y-m-d H:i:s', $data['pay_time']) : null,
                'raw_data' => $data,
            ]
        );

        // Save order items
        if (!empty($data['item_list'])) {
            $order->items()->delete();
            foreach ($data['item_list'] as $item) {
                ShopeeOrderItem::create([
                    'shopee_order_id' => $order->id,
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_sku' => $item['item_sku'] ?? null,
                    'model_id' => $item['model_id'] ?? null,
                    'model_name' => $item['model_name'] ?? null,
                    'model_sku' => $item['model_sku'] ?? null,
                    'quantity' => $item['model_quantity_purchased'] ?? $item['quantity'] ?? 1,
                    'original_price' => $item['model_original_price'] ?? $item['item_price'] ?? 0,
                    'discounted_price' => $item['model_discounted_price'] ?? $item['item_price'] ?? 0,
                    'image_url' => $item['image_info']['image_url'] ?? null,
                    'weight' => $item['weight'] ?? null,
                    'is_wholesale' => $item['is_wholesale'] ?? false,
                ]);
            }
        }

        return $order;
    }

    protected function mapShopeeStatus(string $shopeeStatus): string
    {
        // Don't downgrade internal status if already advanced
        return ShopeeOrder::SHOPEE_STATUS_MAP[$shopeeStatus] ?? 'new';
    }

    public function confirmOrder(ShopeeOrder $order): bool
    {
        $order->update([
            'internal_status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        // Auto deduct stock for mapped products
        if (!$order->stock_deducted) {
            $this->deductStock($order);
        }

        return true;
    }

    public function deductStock(ShopeeOrder $order): array
    {
        $results = ['deducted' => 0, 'skipped' => 0, 'items' => []];
        $productUtil = app(ProductUtil::class);

        // Get the first business location as default
        $location = \App\BusinessLocation::where('business_id', $order->business_id)
            ->first();

        if (!$location) {
            return $results;
        }

        $order->load('items');

        foreach ($order->items as $item) {
            // Find mapping by shopee_item_id + model_id
            $mapping = ShopeeProductMapping::where('shopee_shop_id', $order->shopee_shop_id)
                ->where('shopee_item_id', $item->item_id)
                ->where('is_active', true)
                ->where('auto_destock', true)
                ->when($item->model_id, function ($q) use ($item) {
                    $q->where('shopee_model_id', $item->model_id);
                }, function ($q) {
                    $q->whereNull('shopee_model_id');
                })
                ->first();

            // Fallback: try to match by SKU
            if (!$mapping && ($item->model_sku || $item->item_sku)) {
                $sku = $item->model_sku ?: $item->item_sku;
                $mapping = ShopeeProductMapping::where('business_id', $order->business_id)
                    ->where('shopee_sku', $sku)
                    ->where('is_active', true)
                    ->where('auto_destock', true)
                    ->first();
            }

            if ($mapping) {
                $productUtil->decreaseProductQuantity(
                    $mapping->product_id,
                    $mapping->variation_id,
                    $location->id,
                    $item->quantity
                );
                $results['deducted']++;
                $results['items'][] = [
                    'item_name' => $item->item_name,
                    'sku' => $item->model_sku ?: $item->item_sku,
                    'quantity' => $item->quantity,
                    'product_id' => $mapping->product_id,
                ];
            } else {
                $results['skipped']++;
            }
        }

        if ($results['deducted'] > 0) {
            $order->update([
                'stock_deducted' => true,
                'stock_deducted_at' => now(),
            ]);

            $this->api->log($order->shop, 'order_action', 'success',
                "Order {$order->order_sn}: deducted stock for {$results['deducted']} items, skipped {$results['skipped']}");
        }

        return $results;
    }

    public function markPacking(ShopeeOrder $order): bool
    {
        $order->update([
            'internal_status' => 'packing',
            'packed_by' => auth()->id(),
        ]);
        return true;
    }

    public function markReadyToShip(ShopeeOrder $order): bool
    {
        $order->update([
            'internal_status' => 'ready_to_ship',
            'packed_at' => now(),
        ]);
        return true;
    }

    public function shipOrder(ShopeeShop $shop, ShopeeOrder $order): bool
    {
        try {
            $client = $this->api->createClientForShop($shop);

            // Get shipping parameter first
            $shippingParams = $client->Logistic->getShippingParameter($order->order_sn);

            $pickup = $shippingParams['info_needed']['pickup'] ?? null;
            $dropoff = $shippingParams['info_needed']['dropoff'] ?? null;

            if ($pickup) {
                $addressList = $pickup['address_list'] ?? [];
                $addressId = $addressList[0]['address_id'] ?? null;

                $client->Logistic->shipOrder($order->order_sn, '', [
                    'address_id' => $addressId,
                ]);
            } elseif ($dropoff) {
                $client->Logistic->shipOrder($order->order_sn, '', [], []);
            } else {
                $client->Logistic->shipOrder($order->order_sn);
            }

            // Get tracking number
            $trackingInfo = $client->Logistic->getTrackingNumber($order->order_sn);

            $order->update([
                'internal_status' => 'shipped',
                'tracking_number' => $trackingInfo['tracking_number'] ?? $order->tracking_number,
                'shipped_by' => auth()->id(),
                'shipped_at' => now(),
            ]);

            $this->api->log($shop, 'order_action', 'success',
                "Order {$order->order_sn} shipped, tracking: " . ($trackingInfo['tracking_number'] ?? 'N/A'));

            return true;
        } catch (\Exception $e) {
            $this->api->log($shop, 'order_action', 'error',
                "Ship order {$order->order_sn} failed: " . $e->getMessage());
            return false;
        }
    }

    public function cancelOrder(ShopeeOrder $order, string $reason = ''): bool
    {
        $order->update([
            'internal_status' => 'cancelled',
            'note' => $reason ? ($order->note ? $order->note . "\n" : '') . "Cancel: " . $reason : $order->note,
        ]);
        return true;
    }

    public function getStatusCounts(int $businessId, ?int $shopId = null): array
    {
        $query = ShopeeOrder::where('business_id', $businessId);
        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        $counts = $query->selectRaw('internal_status, count(*) as count')
            ->groupBy('internal_status')
            ->pluck('count', 'internal_status')
            ->toArray();

        $result = [];
        foreach (ShopeeOrder::INTERNAL_STATUSES as $key => $label) {
            $result[$key] = $counts[$key] ?? 0;
        }
        $result['all'] = array_sum($result);

        return $result;
    }
}
