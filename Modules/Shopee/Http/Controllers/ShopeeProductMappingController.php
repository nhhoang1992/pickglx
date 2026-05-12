<?php

namespace Modules\Shopee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Product;
use App\Variation;
use Illuminate\Http\Request;
use Modules\Shopee\Models\ShopeeProductMapping;
use Modules\Shopee\Models\ShopeeShop;

class ShopeeProductMappingController extends Controller
{
    public function index(Request $request)
    {
        $businessId = session('business.id');

        $shops = ShopeeShop::where('business_id', $businessId)
            ->where('status', 'connected')
            ->get();

        $shopId = $request->get('shop_id');
        $search = $request->get('search');

        $query = ShopeeProductMapping::where('business_id', $businessId)
            ->with(['shop', 'product', 'variation']);

        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('shopee_sku', 'like', "%{$search}%")
                    ->orWhere('shopee_item_name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        $mappings = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('shopee::shopee.product_mappings.index', compact('mappings', 'shops', 'shopId', 'search'));
    }

    public function create(Request $request)
    {
        $businessId = session('business.id');

        $shops = ShopeeShop::where('business_id', $businessId)
            ->where('status', 'connected')
            ->get();

        $products = Product::where('business_id', $businessId)
            ->where('enable_stock', 1)
            ->with(['variations'])
            ->orderBy('name')
            ->get();

        return view('shopee::shopee.product_mappings.create', compact('shops', 'products'));
    }

    public function store(Request $request)
    {
        $businessId = session('business.id');

        $request->validate([
            'shopee_shop_id' => 'required|exists:shopee_shops,id',
            'shopee_item_id' => 'required|integer',
            'shopee_sku' => 'nullable|string|max:100',
            'shopee_item_name' => 'required|string|max:500',
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'required|exists:variations,id',
        ]);

        ShopeeProductMapping::updateOrCreate(
            [
                'shopee_shop_id' => $request->shopee_shop_id,
                'shopee_item_id' => $request->shopee_item_id,
                'shopee_model_id' => $request->shopee_model_id,
            ],
            [
                'business_id' => $businessId,
                'shopee_sku' => $request->shopee_sku,
                'shopee_item_name' => $request->shopee_item_name,
                'shopee_model_name' => $request->shopee_model_name,
                'shopee_image_url' => $request->shopee_image_url,
                'product_id' => $request->product_id,
                'variation_id' => $request->variation_id,
                'auto_destock' => $request->has('auto_destock'),
                'is_active' => true,
            ]
        );

        return redirect()->route('shopee.product-mappings')
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.mapping_created')]);
    }

    public function autoMatch(Request $request)
    {
        $businessId = session('business.id');
        $shopId = $request->get('shop_id');

        $shops = $shopId
            ? ShopeeShop::where('id', $shopId)->where('business_id', $businessId)->where('status', 'connected')->get()
            : ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();

        $matched = 0;

        foreach ($shops as $shop) {
            // Get all variations with sub_sku from UltimatePOS
            $variations = Variation::whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId)->where('enable_stock', 1);
            })
            ->whereNotNull('sub_sku')
            ->where('sub_sku', '!=', '')
            ->with('product')
            ->get();

            foreach ($variations as $variation) {
                // Check if this SKU exists in any Shopee order item
                $existingMapping = ShopeeProductMapping::where('shopee_shop_id', $shop->id)
                    ->where('shopee_sku', $variation->sub_sku)
                    ->first();

                if ($existingMapping) {
                    continue;
                }

                // Look for matching SKU in shopee_order_items
                $orderItem = \Modules\Shopee\Models\ShopeeOrderItem::whereHas('order', function ($q) use ($shop) {
                    $q->where('shopee_shop_id', $shop->id);
                })
                ->where(function ($q) use ($variation) {
                    $q->where('item_sku', $variation->sub_sku)
                        ->orWhere('model_sku', $variation->sub_sku);
                })
                ->first();

                if ($orderItem) {
                    ShopeeProductMapping::create([
                        'business_id' => $businessId,
                        'shopee_shop_id' => $shop->id,
                        'shopee_item_id' => $orderItem->item_id,
                        'shopee_model_id' => $orderItem->model_id,
                        'shopee_sku' => $variation->sub_sku,
                        'shopee_item_name' => $orderItem->item_name,
                        'shopee_model_name' => $orderItem->model_name,
                        'shopee_image_url' => $orderItem->image_url,
                        'product_id' => $variation->product_id,
                        'variation_id' => $variation->id,
                        'auto_destock' => true,
                        'is_active' => true,
                    ]);
                    $matched++;
                }
            }
        }

        return redirect()->route('shopee.product-mappings')
            ->with('status', [
                'success' => true,
                'msg' => __('shopee::lang.auto_matched', ['count' => $matched]),
            ]);
    }

    public function edit($id)
    {
        $businessId = session('business.id');

        $mapping = ShopeeProductMapping::where('id', $id)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $shops = ShopeeShop::where('business_id', $businessId)
            ->where('status', 'connected')
            ->get();

        $products = Product::where('business_id', $businessId)
            ->where('enable_stock', 1)
            ->with(['variations'])
            ->orderBy('name')
            ->get();

        return view('shopee::shopee.product_mappings.edit', compact('mapping', 'shops', 'products'));
    }

    public function update(Request $request, $id)
    {
        $businessId = session('business.id');

        $mapping = ShopeeProductMapping::where('id', $id)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'required|exists:variations,id',
        ]);

        $mapping->update([
            'product_id' => $request->product_id,
            'variation_id' => $request->variation_id,
            'auto_destock' => $request->has('auto_destock'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('shopee.product-mappings')
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.mapping_updated')]);
    }

    public function destroy($id)
    {
        $businessId = session('business.id');

        $mapping = ShopeeProductMapping::where('id', $id)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $mapping->delete();

        return redirect()->route('shopee.product-mappings')
            ->with('status', ['success' => true, 'msg' => __('shopee::lang.mapping_deleted')]);
    }

    public function getVariations(Request $request)
    {
        $productId = $request->get('product_id');
        $variations = Variation::where('product_id', $productId)->get(['id', 'name', 'sub_sku', 'default_sell_price']);
        return response()->json($variations);
    }
}
