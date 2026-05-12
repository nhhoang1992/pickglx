<?php

namespace Modules\Shopee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Product;
use App\Variation;
use Illuminate\Http\Request;
use Modules\Shopee\Models\ShopeeProduct;
use Modules\Shopee\Models\ShopeeProductMapping;
use Modules\Shopee\Models\ShopeeProductVariant;
use Modules\Shopee\Models\ShopeeShop;
use Modules\Shopee\Services\ShopeeProductService;

class ShopeeProductMappingController extends Controller
{
    protected ShopeeProductService $productService;

    public function __construct(ShopeeProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $businessId = session('business.id');

        $shops = ShopeeShop::where('business_id', $businessId)
            ->where('status', 'connected')
            ->get();

        $shopId = $request->get('shop_id', $shops->first()?->id);
        $search = $request->get('search');
        $linkFilter = $request->get('link_filter'); // 'linked', 'unlinked', null

        $query = ShopeeProduct::where('business_id', $businessId)
            ->with(['variants']);

        if ($shopId) {
            $query->where('shopee_shop_id', $shopId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_sku', 'like', "%{$search}%")
                    ->orWhereHas('variants', function ($vq) use ($search) {
                        $vq->where('model_sku', 'like', "%{$search}%")
                            ->orWhere('model_name', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->orderBy('item_name')->paginate(20);

        // Load mappings for these products
        $productIds = $products->pluck('item_id')->toArray();
        $mappings = ShopeeProductMapping::where('business_id', $businessId)
            ->when($shopId, fn($q) => $q->where('shopee_shop_id', $shopId))
            ->whereIn('shopee_item_id', $productIds)
            ->where('is_active', true)
            ->with(['product', 'variation'])
            ->get()
            ->keyBy(function ($m) {
                return $m->shopee_item_id . '_' . ($m->shopee_model_id ?? '0');
            });

        // Get all POS variations for autocomplete
        $posVariations = Variation::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId)->where('enable_stock', 1);
        })
            ->with('product')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'sub_sku' => $v->sub_sku,
                'label' => $v->product->name . ($v->name !== 'DUMMY' ? ' - ' . $v->name : '') . ($v->sub_sku ? ' [' . $v->sub_sku . ']' : ''),
            ]);

        // Filter by link status after loading mappings
        if ($linkFilter === 'linked' || $linkFilter === 'unlinked') {
            $filteredProducts = $products->getCollection()->filter(function ($product) use ($mappings, $linkFilter) {
                $hasMapping = false;
                if ($product->has_model && $product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        $key = $product->item_id . '_' . $variant->model_id;
                        if ($mappings->has($key)) {
                            $hasMapping = true;
                            break;
                        }
                    }
                } else {
                    $key = $product->item_id . '_0';
                    $hasMapping = $mappings->has($key);
                }
                return $linkFilter === 'linked' ? $hasMapping : !$hasMapping;
            });
            $products->setCollection($filteredProducts);
        }

        return view('shopee::shopee.product_mappings.index', compact(
            'products', 'shops', 'shopId', 'search', 'linkFilter', 'mappings', 'posVariations'
        ));
    }

    public function syncProducts(Request $request)
    {
        $businessId = session('business.id');
        $shopId = $request->get('shop_id');

        $shops = $shopId
            ? ShopeeShop::where('id', $shopId)->where('business_id', $businessId)->where('status', 'connected')->get()
            : ShopeeShop::where('business_id', $businessId)->where('status', 'connected')->get();

        $totalSynced = 0;
        $totalErrors = 0;

        foreach ($shops as $shop) {
            $result = $this->productService->syncProducts($shop);
            $totalSynced += $result['synced'];
            $totalErrors += $result['errors'];
        }

        return redirect()->route('shopee.product-mappings', ['shop_id' => $shopId])
            ->with('status', [
                'success' => $totalErrors === 0,
                'msg' => __('shopee::lang.products_synced', ['count' => $totalSynced]),
            ]);
    }

    public function linkProduct(Request $request)
    {
        $businessId = session('business.id');

        $request->validate([
            'shopee_shop_id' => 'required',
            'shopee_item_id' => 'required',
            'variation_id' => 'required|exists:variations,id',
        ]);

        $variation = Variation::with('product')->findOrFail($request->variation_id);

        // Get shopee product info
        $shopeeProduct = ShopeeProduct::where('shopee_shop_id', $request->shopee_shop_id)
            ->where('item_id', $request->shopee_item_id)
            ->first();

        $shopeeVariant = null;
        if ($request->shopee_model_id) {
            $shopeeVariant = ShopeeProductVariant::where('shopee_product_id', $shopeeProduct?->id)
                ->where('model_id', $request->shopee_model_id)
                ->first();
        }

        ShopeeProductMapping::updateOrCreate(
            [
                'shopee_shop_id' => $request->shopee_shop_id,
                'shopee_item_id' => $request->shopee_item_id,
                'shopee_model_id' => $request->shopee_model_id,
            ],
            [
                'business_id' => $businessId,
                'shopee_sku' => $shopeeVariant?->model_sku ?? $shopeeProduct?->item_sku,
                'shopee_item_name' => $shopeeProduct?->item_name ?? '',
                'shopee_model_name' => $shopeeVariant?->model_name,
                'shopee_image_url' => $shopeeProduct?->image_url,
                'product_id' => $variation->product_id,
                'variation_id' => $variation->id,
                'auto_destock' => true,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'msg' => __('shopee::lang.mapping_created'),
            'product_name' => $variation->product->name,
            'variation_name' => $variation->name !== 'DUMMY' ? $variation->name : '',
            'sub_sku' => $variation->sub_sku,
        ]);
    }

    public function unlinkProduct(Request $request)
    {
        $businessId = session('business.id');

        $mapping = ShopeeProductMapping::where('business_id', $businessId)
            ->where('shopee_shop_id', $request->shopee_shop_id)
            ->where('shopee_item_id', $request->shopee_item_id)
            ->when($request->shopee_model_id, function ($q) use ($request) {
                $q->where('shopee_model_id', $request->shopee_model_id);
            }, function ($q) {
                $q->whereNull('shopee_model_id');
            })
            ->first();

        if ($mapping) {
            $mapping->delete();
        }

        return response()->json([
            'success' => true,
            'msg' => __('shopee::lang.mapping_deleted'),
        ]);
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
            // Get all POS variations with sub_sku
            $variations = Variation::whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId)->where('enable_stock', 1);
            })
                ->whereNotNull('sub_sku')
                ->where('sub_sku', '!=', '')
                ->with('product')
                ->get()
                ->keyBy('sub_sku');

            // Match against shopee_products item_sku
            $shopeeProducts = ShopeeProduct::where('shopee_shop_id', $shop->id)->get();

            foreach ($shopeeProducts as $sp) {
                if ($sp->has_model) {
                    $variants = ShopeeProductVariant::where('shopee_product_id', $sp->id)->get();
                    foreach ($variants as $sv) {
                        if ($sv->model_sku && $variations->has($sv->model_sku)) {
                            $v = $variations->get($sv->model_sku);
                            $exists = ShopeeProductMapping::where('shopee_shop_id', $shop->id)
                                ->where('shopee_item_id', $sp->item_id)
                                ->where('shopee_model_id', $sv->model_id)
                                ->exists();
                            if (!$exists) {
                                ShopeeProductMapping::create([
                                    'business_id' => $businessId,
                                    'shopee_shop_id' => $shop->id,
                                    'shopee_item_id' => $sp->item_id,
                                    'shopee_model_id' => $sv->model_id,
                                    'shopee_sku' => $sv->model_sku,
                                    'shopee_item_name' => $sp->item_name,
                                    'shopee_model_name' => $sv->model_name,
                                    'shopee_image_url' => $sp->image_url,
                                    'product_id' => $v->product_id,
                                    'variation_id' => $v->id,
                                    'auto_destock' => true,
                                    'is_active' => true,
                                ]);
                                $matched++;
                            }
                        }
                    }
                } else {
                    if ($sp->item_sku && $variations->has($sp->item_sku)) {
                        $v = $variations->get($sp->item_sku);
                        $exists = ShopeeProductMapping::where('shopee_shop_id', $shop->id)
                            ->where('shopee_item_id', $sp->item_id)
                            ->whereNull('shopee_model_id')
                            ->exists();
                        if (!$exists) {
                            ShopeeProductMapping::create([
                                'business_id' => $businessId,
                                'shopee_shop_id' => $shop->id,
                                'shopee_item_id' => $sp->item_id,
                                'shopee_model_id' => null,
                                'shopee_sku' => $sp->item_sku,
                                'shopee_item_name' => $sp->item_name,
                                'shopee_image_url' => $sp->image_url,
                                'product_id' => $v->product_id,
                                'variation_id' => $v->id,
                                'auto_destock' => true,
                                'is_active' => true,
                            ]);
                            $matched++;
                        }
                    }
                }
            }
        }

        return redirect()->route('shopee.product-mappings', ['shop_id' => $shopId])
            ->with('status', [
                'success' => true,
                'msg' => __('shopee::lang.auto_matched', ['count' => $matched]),
            ]);
    }

    public function getVariations(Request $request)
    {
        $productId = $request->get('product_id');
        $variations = Variation::where('product_id', $productId)->get(['id', 'name', 'sub_sku', 'default_sell_price']);
        return response()->json($variations);
    }

    public function searchVariations(Request $request)
    {
        $businessId = session('business.id');
        $search = $request->get('q', '');

        $variations = Variation::whereHas('product', function ($q) use ($businessId) {
            $q->where('business_id', $businessId)->where('enable_stock', 1);
        })
            ->where(function ($q) use ($search) {
                $q->where('sub_sku', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            })
            ->with('product')
            ->limit(20)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'sub_sku' => $v->sub_sku,
                'text' => $v->product->name . ($v->name !== 'DUMMY' ? ' - ' . $v->name : '') . ($v->sub_sku ? ' [' . $v->sub_sku . ']' : ''),
            ]);

        return response()->json($variations);
    }
}
