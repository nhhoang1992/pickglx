<?php

namespace Modules\Shopee\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shopee\Models\ShopeeOrder;
use Modules\Shopee\Models\ShopeeOrderItem;
use Modules\Shopee\Models\ShopeeShop;

class ShopeeReportController extends Controller
{
    public function dashboard(Request $request)
    {
        $businessId = session('business.id');
        $shops = ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();
        $shopId = $request->get('shop_id');

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now()->endOfDay();

        $query = ShopeeOrder::where('business_id', $businessId)
            ->whereBetween('order_created_at', [$dateFrom, $dateTo]);

        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        // Revenue metrics
        $totalRevenue = (clone $query)->whereNotIn('internal_status', ['cancelled', 'returned'])->sum('total_amount');
        $totalOrders = (clone $query)->count();
        $completedOrders = (clone $query)->where('internal_status', 'completed')->count();
        $cancelledOrders = (clone $query)->where('internal_status', 'cancelled')->count();
        $returnedOrders = (clone $query)->where('internal_status', 'returned')->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / max($totalOrders - $cancelledOrders - $returnedOrders, 1) : 0;
        $totalShippingFee = (clone $query)->whereNotIn('internal_status', ['cancelled'])->sum('shipping_fee');

        // Revenue by day chart
        $revenueByDay = (clone $query)
            ->whereNotIn('internal_status', ['cancelled', 'returned'])
            ->select(
                DB::raw('DATE(order_created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy(DB::raw('DATE(order_created_at)'))
            ->orderBy('date')
            ->get();

        // Orders by status
        $ordersByStatus = (clone $query)
            ->select('internal_status', DB::raw('COUNT(*) as count'))
            ->groupBy('internal_status')
            ->pluck('count', 'internal_status')
            ->toArray();

        // Previous period comparison
        $periodDays = $dateFrom->diffInDays($dateTo);
        $prevFrom = (clone $dateFrom)->subDays($periodDays);
        $prevTo = (clone $dateTo)->subDays($periodDays);

        $prevQuery = ShopeeOrder::where('business_id', $businessId)
            ->whereBetween('order_created_at', [$prevFrom, $prevTo]);
        if ($shopId) {
            $prevQuery->where('shopee_shop_id', $shopId);
        }

        $prevRevenue = (clone $prevQuery)->whereNotIn('internal_status', ['cancelled', 'returned'])->sum('total_amount');
        $prevOrders = (clone $prevQuery)->count();
        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        $orderGrowth = $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;

        // Revenue by shop
        $revenueByShop = ShopeeOrder::where('shopee_orders.business_id', $businessId)
            ->whereNotIn('internal_status', ['cancelled', 'returned'])
            ->whereBetween('order_created_at', [$dateFrom, $dateTo])
            ->join('shopee_shops', 'shopee_orders.shopee_shop_id', '=', 'shopee_shops.id')
            ->select(
                'shopee_shops.shop_name',
                DB::raw('SUM(shopee_orders.total_amount) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('shopee_shops.shop_name')
            ->get();

        return view('shopee::shopee.reports.dashboard', compact(
            'shops', 'shopId', 'dateFrom', 'dateTo',
            'totalRevenue', 'totalOrders', 'completedOrders', 'cancelledOrders', 'returnedOrders',
            'avgOrderValue', 'totalShippingFee',
            'revenueByDay', 'ordersByStatus',
            'revenueGrowth', 'orderGrowth', 'prevRevenue', 'prevOrders',
            'revenueByShop'
        ));
    }

    public function orderAnalysis(Request $request)
    {
        $businessId = session('business.id');
        $shops = ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();
        $shopId = $request->get('shop_id');

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now()->endOfDay();

        $query = ShopeeOrder::where('business_id', $businessId)
            ->whereBetween('order_created_at', [$dateFrom, $dateTo]);
        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        // Orders by status
        $ordersByStatus = (clone $query)
            ->select('internal_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('internal_status')
            ->get()
            ->keyBy('internal_status');

        // Orders by day
        $ordersByDay = (clone $query)
            ->select(
                DB::raw('DATE(order_created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN internal_status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN internal_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN internal_status = 'returned' THEN 1 ELSE 0 END) as returned")
            )
            ->groupBy(DB::raw('DATE(order_created_at)'))
            ->orderBy('date')
            ->get();

        // Cancel/Return rate
        $total = (clone $query)->count();
        $cancelRate = $total > 0 ? ($cancelledCount = (clone $query)->where('internal_status', 'cancelled')->count()) / $total * 100 : 0;
        $returnRate = $total > 0 ? ($returnedCount = (clone $query)->where('internal_status', 'returned')->count()) / $total * 100 : 0;
        $completionRate = $total > 0 ? (clone $query)->where('internal_status', 'completed')->count() / $total * 100 : 0;

        // Average processing time (confirmed_at - order_created_at)
        $avgProcessingTime = (clone $query)
            ->whereNotNull('confirmed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, order_created_at, confirmed_at)) as avg_minutes'))
            ->value('avg_minutes');

        // Orders by hour of day
        $ordersByHour = (clone $query)
            ->select(DB::raw('HOUR(order_created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(order_created_at)'))
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Payment method distribution
        $paymentMethods = (clone $query)
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get();

        return view('shopee::shopee.reports.order-analysis', compact(
            'shops', 'shopId', 'dateFrom', 'dateTo',
            'ordersByStatus', 'ordersByDay',
            'cancelRate', 'returnRate', 'completionRate',
            'avgProcessingTime', 'ordersByHour', 'paymentMethods', 'total'
        ));
    }

    public function topProducts(Request $request)
    {
        $businessId = session('business.id');
        $shops = ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();
        $shopId = $request->get('shop_id');
        $sortBy = $request->get('sort_by', 'quantity');

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now()->endOfDay();

        $query = ShopeeOrderItem::join('shopee_orders', 'shopee_order_items.shopee_order_id', '=', 'shopee_orders.id')
            ->where('shopee_orders.business_id', $businessId)
            ->whereNotIn('shopee_orders.internal_status', ['cancelled', 'returned'])
            ->whereBetween('shopee_orders.order_created_at', [$dateFrom, $dateTo]);

        if ($shopId) {
            $query->where('shopee_orders.shopee_shop_id', $shopId);
        }

        $orderColumn = $sortBy === 'revenue' ? 'total_revenue' : 'total_qty';

        $topProducts = (clone $query)
            ->select(
                'shopee_order_items.item_id',
                'shopee_order_items.item_name',
                'shopee_order_items.image_url',
                'shopee_order_items.item_sku',
                DB::raw('SUM(shopee_order_items.quantity) as total_qty'),
                DB::raw('SUM(shopee_order_items.discounted_price * shopee_order_items.quantity) as total_revenue'),
                DB::raw('COUNT(DISTINCT shopee_orders.id) as order_count')
            )
            ->groupBy('shopee_order_items.item_id', 'shopee_order_items.item_name', 'shopee_order_items.image_url', 'shopee_order_items.item_sku')
            ->orderByDesc($orderColumn)
            ->limit(50)
            ->get();

        // Top variants
        $topVariants = (clone $query)
            ->select(
                'shopee_order_items.item_name',
                'shopee_order_items.model_name',
                'shopee_order_items.model_sku',
                'shopee_order_items.image_url',
                DB::raw('SUM(shopee_order_items.quantity) as total_qty'),
                DB::raw('SUM(shopee_order_items.discounted_price * shopee_order_items.quantity) as total_revenue'),
                DB::raw('COUNT(DISTINCT shopee_orders.id) as order_count')
            )
            ->groupBy('shopee_order_items.item_name', 'shopee_order_items.model_name', 'shopee_order_items.model_sku', 'shopee_order_items.image_url')
            ->orderByDesc($orderColumn)
            ->limit(50)
            ->get();

        // Total metrics
        $totalQuantity = $topProducts->sum('total_qty');
        $totalRevenue = $topProducts->sum('total_revenue');

        return view('shopee::shopee.reports.top-products', compact(
            'shops', 'shopId', 'dateFrom', 'dateTo', 'sortBy',
            'topProducts', 'topVariants', 'totalQuantity', 'totalRevenue'
        ));
    }

    public function shipping(Request $request)
    {
        $businessId = session('business.id');
        $shops = ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();
        $shopId = $request->get('shop_id');

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now()->endOfDay();

        $query = ShopeeOrder::where('business_id', $businessId)
            ->whereBetween('order_created_at', [$dateFrom, $dateTo]);
        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        // Shipping carrier distribution
        $carrierStats = (clone $query)
            ->whereNotNull('shipping_carrier')
            ->where('shipping_carrier', '!=', '')
            ->select(
                'shipping_carrier',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(shipping_fee) as total_shipping_fee'),
                DB::raw('AVG(shipping_fee) as avg_shipping_fee'),
                DB::raw("SUM(CASE WHEN internal_status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN internal_status = 'returned' THEN 1 ELSE 0 END) as returned"),
                DB::raw("SUM(CASE WHEN internal_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->groupBy('shipping_carrier')
            ->orderByDesc('order_count')
            ->get();

        // Shipping fee summary
        $totalShippingFee = (clone $query)->whereNotIn('internal_status', ['cancelled'])->sum('shipping_fee');
        $avgShippingFee = (clone $query)->whereNotIn('internal_status', ['cancelled'])->avg('shipping_fee');

        // Express vs Normal
        $expressCount = (clone $query)->where('is_express', true)->count();
        $normalCount = (clone $query)->where('is_express', false)->count();

        // Shipping by region (city)
        $shippingByCity = (clone $query)
            ->whereNotNull('shipping_city')
            ->where('shipping_city', '!=', '')
            ->select(
                'shipping_city',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(shipping_fee) as avg_shipping')
            )
            ->groupBy('shipping_city')
            ->orderByDesc('order_count')
            ->limit(20)
            ->get();

        return view('shopee::shopee.reports.shipping', compact(
            'shops', 'shopId', 'dateFrom', 'dateTo',
            'carrierStats', 'totalShippingFee', 'avgShippingFee',
            'expressCount', 'normalCount', 'shippingByCity'
        ));
    }

    public function reconciliation(Request $request)
    {
        $businessId = session('business.id');
        $shops = ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();
        $shopId = $request->get('shop_id');

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now()->endOfDay();

        $query = ShopeeOrder::where('business_id', $businessId)
            ->whereBetween('order_created_at', [$dateFrom, $dateTo]);
        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        // Summary by status
        $summary = (clone $query)
            ->select(
                'internal_status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(shipping_fee) as total_shipping'),
                DB::raw('SUM(buyer_paid_amount) as total_buyer_paid')
            )
            ->groupBy('internal_status')
            ->get()
            ->keyBy('internal_status');

        // Daily reconciliation
        $dailyRecon = (clone $query)
            ->whereNotIn('internal_status', ['cancelled'])
            ->select(
                DB::raw('DATE(order_created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(shipping_fee) as shipping'),
                DB::raw('SUM(buyer_paid_amount) as buyer_paid'),
                DB::raw("SUM(CASE WHEN internal_status = 'completed' THEN total_amount ELSE 0 END) as completed_revenue")
            )
            ->groupBy(DB::raw('DATE(order_created_at)'))
            ->orderBy('date')
            ->get();

        // Totals
        $totalAmount = (clone $query)->whereNotIn('internal_status', ['cancelled'])->sum('total_amount');
        $totalShipping = (clone $query)->whereNotIn('internal_status', ['cancelled'])->sum('shipping_fee');
        $totalBuyerPaid = (clone $query)->whereNotIn('internal_status', ['cancelled'])->sum('buyer_paid_amount');
        $completedAmount = (clone $query)->where('internal_status', 'completed')->sum('total_amount');
        $pendingAmount = $totalAmount - $completedAmount;

        // By shop
        $reconByShop = ShopeeOrder::where('shopee_orders.business_id', $businessId)
            ->whereNotIn('internal_status', ['cancelled'])
            ->whereBetween('order_created_at', [$dateFrom, $dateTo])
            ->join('shopee_shops', 'shopee_orders.shopee_shop_id', '=', 'shopee_shops.id')
            ->select(
                'shopee_shops.shop_name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(shopee_orders.total_amount) as revenue'),
                DB::raw("SUM(CASE WHEN shopee_orders.internal_status = 'completed' THEN shopee_orders.total_amount ELSE 0 END) as completed_revenue"),
                DB::raw('SUM(shopee_orders.shipping_fee) as shipping')
            )
            ->groupBy('shopee_shops.shop_name')
            ->get();

        return view('shopee::shopee.reports.reconciliation', compact(
            'shops', 'shopId', 'dateFrom', 'dateTo',
            'summary', 'dailyRecon',
            'totalAmount', 'totalShipping', 'totalBuyerPaid',
            'completedAmount', 'pendingAmount', 'reconByShop'
        ));
    }
}
