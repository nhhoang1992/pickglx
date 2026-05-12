<?php

namespace Modules\Shopee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shopee\Models\ShopeeOrder;
use Modules\Shopee\Models\ShopeeShop;
use Modules\Shopee\Services\ShopeeOrderService;

class ShopeeOrderController extends Controller
{
    protected ShopeeOrderService $orderService;

    public function __construct(ShopeeOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $businessId = session('business.id');

        $shops = ShopeeShop::where('business_id', $businessId)
            ->where('status', 'connected')
            ->get();

        $status = $request->get('status', 'all');
        $shopId = $request->get('shop_id');
        $search = $request->get('search');

        $statusCounts = $this->orderService->getStatusCounts($businessId, $shopId);

        $query = ShopeeOrder::where('business_id', $businessId)
            ->with(['items', 'shop']);

        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        if ($status !== 'all') {
            $query->where('internal_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_sn', 'like', "%{$search}%")
                    ->orWhere('buyer_name', 'like', "%{$search}%")
                    ->orWhere('buyer_phone', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%");
            });
        }

        if ($request->get('is_express')) {
            $query->where('is_express', true);
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->where('order_created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('order_created_at', '<=', $dateTo . ' 23:59:59');
        }

        $orders = $query->orderBy('order_created_at', 'desc')->paginate(25);

        return view('shopee::shopee.orders.index', compact(
            'orders', 'shops', 'status', 'statusCounts', 'shopId', 'search'
        ));
    }

    public function show($id)
    {
        $businessId = session('business.id');

        $order = ShopeeOrder::where('id', $id)
            ->where('business_id', $businessId)
            ->with(['items', 'shop', 'confirmedBy', 'packedBy', 'shippedBy'])
            ->firstOrFail();

        return view('shopee::shopee.orders.show', compact('order'));
    }

    public function syncOrders(Request $request)
    {
        $businessId = session('business.id');
        $shopId = $request->get('shop_id');

        $shops = $shopId
            ? ShopeeShop::where('id', $shopId)->where('business_id', $businessId)->where('status', 'connected')->get()
            : ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();

        if ($shops->isEmpty()) {
            return redirect()->route('shopee.orders')
                ->with('status', ['success' => false, 'msg' => __('shopee::lang.no_connected_shops')]);
        }

        $totalSynced = 0;
        $totalErrors = 0;
        $messages = [];

        // Sync multiple statuses to cover all orders
        $statusesToSync = ['UNPAID', 'READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'TO_CONFIRM_RECEIVE', 'IN_CANCEL', 'CANCELLED', 'COMPLETED'];

        foreach ($shops as $shop) {
            foreach ($statusesToSync as $status) {
                $result = $this->orderService->syncOrders($shop, [
                    'status' => $status,
                    'time_from' => now()->subDays(15)->timestamp,
                    'time_to' => now()->timestamp,
                ]);
                $totalSynced += $result['synced'];
                $totalErrors += $result['errors'];
                $messages = array_merge($messages, $result['messages']);
            }
        }

        $msg = __('shopee::lang.orders_synced', ['count' => $totalSynced]);
        if ($totalErrors > 0) {
            $msg .= ' (' . $totalErrors . ' errors)';
        }

        return redirect()->route('shopee.orders')
            ->with('status', ['success' => $totalErrors === 0, 'msg' => $msg]);
    }

    public function confirm(Request $request, $id)
    {
        $businessId = session('business.id');
        $order = ShopeeOrder::where('id', $id)->where('business_id', $businessId)->firstOrFail();

        $this->orderService->confirmOrder($order);

        return redirect()->back()
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.order_confirmed', ['order' => $order->order_sn])]);
    }

    public function markPacking(Request $request, $id)
    {
        $businessId = session('business.id');
        $order = ShopeeOrder::where('id', $id)->where('business_id', $businessId)->firstOrFail();

        $this->orderService->markPacking($order);

        return redirect()->back()
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.order_packing', ['order' => $order->order_sn])]);
    }

    public function markReadyToShip(Request $request, $id)
    {
        $businessId = session('business.id');
        $order = ShopeeOrder::where('id', $id)->where('business_id', $businessId)->firstOrFail();

        $this->orderService->markReadyToShip($order);

        return redirect()->back()
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.order_ready_to_ship', ['order' => $order->order_sn])]);
    }

    public function ship(Request $request, $id)
    {
        $businessId = session('business.id');
        $order = ShopeeOrder::where('id', $id)->where('business_id', $businessId)->firstOrFail();
        $shop = $order->shop;

        $result = $this->orderService->shipOrder($shop, $order);

        return redirect()->back()
            ->with('status', [
                'success' => $result,
                'msg' => $result
                    ? __('shopee::lang.order_shipped', ['order' => $order->order_sn])
                    : __('shopee::lang.order_ship_failed', ['order' => $order->order_sn]),
            ]);
    }

    public function bulkAction(Request $request)
    {
        $businessId = session('business.id');
        $action = $request->get('action');
        $orderIds = $request->get('order_ids', []);

        if (empty($orderIds)) {
            return redirect()->back()
                ->with('status', ['success' => false, 'msg' => __('shopee::lang.no_orders_selected')]);
        }

        $orders = ShopeeOrder::whereIn('id', $orderIds)
            ->where('business_id', $businessId)
            ->get();

        $successCount = 0;
        foreach ($orders as $order) {
            switch ($action) {
                case 'confirm':
                    $this->orderService->confirmOrder($order);
                    $successCount++;
                    break;
                case 'packing':
                    $this->orderService->markPacking($order);
                    $successCount++;
                    break;
                case 'ready_to_ship':
                    $this->orderService->markReadyToShip($order);
                    $successCount++;
                    break;
                case 'ship':
                    if ($this->orderService->shipOrder($order->shop, $order)) {
                        $successCount++;
                    }
                    break;
            }
        }

        return redirect()->back()
            ->with('status', [
                'success' => true,
                'msg' => __('shopee::lang.bulk_action_done', ['count' => $successCount]),
            ]);
    }

    public function updateNote(Request $request, $id)
    {
        $businessId = session('business.id');
        $order = ShopeeOrder::where('id', $id)->where('business_id', $businessId)->firstOrFail();

        $order->update(['note' => $request->get('note')]);

        return redirect()->back()
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.note_updated')]);
    }
}
