@extends('layouts.app')
@section('title', __('shopee::lang.product_mapping'))

@section('content')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-store"></i> @lang('shopee::lang.shopee_product_page')
    </h1>
</section>

<section class="content">

    @if (session('status'))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }} alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('status.msg') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Shop Tabs -->
    @if($shops->count() > 0)
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-sm-12">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('shopee.product-mappings') }}"
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 14px;
                          {{ !$shopId ? 'background: #333; color: #fff; font-weight: bold;' : 'background: #f0f0f0; color: #333;' }}">
                    <i class="fas fa-globe" style="font-size: 12px;"></i>
                    @lang('shopee::lang.all_shops')
                </a>
                @foreach($shops as $shop)
                    <a href="{{ route('shopee.product-mappings', ['shop_id' => $shop->id]) }}"
                       style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 14px;
                              {{ ($shopId == $shop->id) ? 'background: #ee4d2d; color: #fff; font-weight: bold;' : 'background: #f0f0f0; color: #333;' }}">
                        <i class="fas fa-store" style="font-size: 12px;"></i>
                        {{ $shop->shop_name ?: 'Shop ' . $shop->shop_id }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-sm-12">
            <div class="box box-solid" style="margin-bottom: 0;">
                <div class="box-body" style="padding: 12px 15px;">
                    <form method="GET" action="{{ route('shopee.product-mappings') }}" class="form-inline">
                        <input type="hidden" name="shop_id" value="{{ $shopId }}">

                        <div class="form-group" style="margin-right: 12px;">
                            <div class="input-group" style="width: 350px;">
                                <span class="input-group-addon"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="@lang('shopee::lang.search_mapping_placeholder')"
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        <!-- Link filter checkboxes -->
                        <label class="checkbox-inline" style="margin-right: 15px; font-weight: normal;">
                            <input type="radio" name="link_filter" value="unlinked" {{ ($linkFilter ?? '') === 'unlinked' ? 'checked' : '' }}>
                            @lang('shopee::lang.show_unlinked_only')
                        </label>
                        <label class="checkbox-inline" style="margin-right: 15px; font-weight: normal;">
                            <input type="radio" name="link_filter" value="linked" {{ ($linkFilter ?? '') === 'linked' ? 'checked' : '' }}>
                            @lang('shopee::lang.show_linked_only')
                        </label>
                        <label class="checkbox-inline" style="margin-right: 15px; font-weight: normal;">
                            <input type="radio" name="link_filter" value="" {{ empty($linkFilter) ? 'checked' : '' }}>
                            @lang('shopee::lang.all')
                        </label>

                        <button type="submit" class="btn btn-default" style="margin-left: 5px;">
                            <i class="fas fa-filter"></i>
                        </button>

                        <div class="pull-right">
                            <form method="POST" action="{{ route('shopee.product-mappings.auto-match') }}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="shop_id" value="{{ $shopId }}">
                                <button type="submit" class="btn btn-info"
                                        onclick="return confirm('@lang('shopee::lang.confirm_auto_match')')">
                                    <i class="fas fa-magic"></i> @lang('shopee::lang.auto_match_sku')
                                </button>
                            </form>

                            <form method="POST" action="{{ route('shopee.product-mappings.sync-products') }}" style="display: inline; margin-left: 5px;">
                                @csrf
                                <input type="hidden" name="shop_id" value="{{ $shopId }}">
                                <button type="submit" class="btn btn-success"
                                        onclick="return confirm('@lang('shopee::lang.confirm_sync_products')')">
                                    <i class="fas fa-sync-alt"></i> @lang('shopee::lang.sync_products')
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products List (Salework-style) -->
    <div class="row">
        <div class="col-sm-12">
            @if($products->count() > 0)
                <!-- Table Header -->
                <div class="box box-solid" style="margin-bottom: 0; border-bottom: none;">
                    <div class="box-body" style="padding: 8px 15px; background: #f8f9fa;">
                        <div class="row" style="font-weight: bold; color: #666; font-size: 13px;">
                            <div class="col-md-3">@lang('shopee::lang.products')</div>
                            <div class="col-md-2">@lang('shopee::lang.variant')</div>
                            <div class="col-md-3">@lang('shopee::lang.pos_linked_sku')</div>
                            <div class="col-md-2">@lang('shopee::lang.stock')</div>
                            <div class="col-md-2">@lang('shopee::lang.actions')</div>
                        </div>
                    </div>
                </div>

                @foreach($products as $product)
                <div class="box box-solid" style="margin-bottom: 0; border-top: 1px solid #e8e8e8;">
                    <div class="box-body" style="padding: 12px 15px;">
                        <div class="row">
                            <!-- Product Info (left side) -->
                            <div class="col-md-3">
                                <div style="display: flex; gap: 10px;">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; flex-shrink: 0;">
                                    @else
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-image" style="color: #ccc; font-size: 20px;"></i>
                                        </div>
                                    @endif
                                    <div style="overflow: hidden;">
                                        <div style="font-weight: 500; font-size: 13px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ $product->item_name }}
                                        </div>
                                        @if($product->item_sku)
                                            <div style="margin-top: 2px;"><small class="text-muted">SKU: <code>{{ $product->item_sku }}</code></small></div>
                                        @endif
                                        <div style="margin-top: 3px;">
                                            <span style="font-size: 11px; display: inline-block; padding: 1px 8px; border-radius: 3px; background: {{ $product->status_color }}; color: #fff;">
                                                {{ $product->status_label }}
                                            </span>
                                        </div>
                                        <div style="margin-top: 2px;"><small class="text-muted">ID: {{ $product->item_id }}</small></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Variants / Link area -->
                            <div class="col-md-9">
                                @if($product->has_model && $product->variants->count() > 0)
                                    @foreach($product->variants as $variant)
                                        @php
                                            $mappingKey = $product->item_id . '_' . $variant->model_id;
                                            $mapping = $mappings->get($mappingKey);
                                        @endphp
                                        <div class="variant-row" style="display: flex; align-items: center; padding: 6px 0; {{ !$loop->last ? 'border-bottom: 1px solid #f0f0f0;' : '' }}">
                                            <!-- Variant image -->
                                            <div style="width: 35px; margin-right: 8px; flex-shrink: 0;">
                                                @if($variant->image_url)
                                                    <img src="{{ $variant->image_url }}" style="width: 35px; height: 35px; object-fit: cover; border-radius: 3px; border: 1px solid #eee;">
                                                @endif
                                            </div>
                                            <!-- Variant name -->
                                            <div style="flex: 1; min-width: 0; margin-right: 10px;">
                                                <div style="font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $variant->model_name ?: $product->item_name }}</div>
                                                <small class="text-muted">SKU: {{ $variant->model_sku ?: '-' }}</small>
                                            </div>
                                            <!-- Linked POS product -->
                                            <div style="width: 280px; margin-right: 10px;">
                                                @if($mapping)
                                                    <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 4px; padding: 4px 10px; font-size: 12px;">
                                                        <i class="fas fa-link" style="color: #4CAF50; margin-right: 4px;"></i>
                                                        <strong>{{ $mapping->variation?->sub_sku }}</strong>
                                                        <span class="text-muted"> - {{ $mapping->product?->name }}</span>
                                                    </div>
                                                @else
                                                    <div style="background: #fff3e0; border: 1px solid #ffe0b2; border-radius: 4px; padding: 4px 10px; font-size: 12px; color: #e65100;">
                                                        <i class="fas fa-unlink" style="margin-right: 4px;"></i>
                                                        @lang('shopee::lang.not_linked')
                                                    </div>
                                                @endif
                                            </div>
                                            <!-- Stock -->
                                            <div style="width: 60px; text-align: center; margin-right: 10px;">
                                                <span style="font-size: 13px;">{{ $variant->stock }}</span>
                                            </div>
                                            <!-- Actions -->
                                            <div style="width: 80px; text-align: center;">
                                                @if($mapping)
                                                    <button type="button" class="btn btn-xs btn-danger btn-unlink"
                                                            data-shop-id="{{ $product->shopee_shop_id }}"
                                                            data-item-id="{{ $product->item_id }}"
                                                            data-model-id="{{ $variant->model_id }}"
                                                            title="@lang('shopee::lang.unlink')">
                                                        <i class="fas fa-unlink"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-xs btn-success btn-link-product"
                                                            data-shop-id="{{ $product->shopee_shop_id }}"
                                                            data-item-id="{{ $product->item_id }}"
                                                            data-model-id="{{ $variant->model_id }}"
                                                            data-sku="{{ $variant->model_sku }}"
                                                            title="@lang('shopee::lang.link')">
                                                        <i class="fas fa-link"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    {{-- Product without variants --}}
                                    @php
                                        $mappingKey = $product->item_id . '_0';
                                        $mapping = $mappings->get($mappingKey);
                                    @endphp
                                    <div class="variant-row" style="display: flex; align-items: center; padding: 6px 0;">
                                        <div style="width: 35px; margin-right: 8px; flex-shrink: 0;"></div>
                                        <div style="flex: 1; min-width: 0; margin-right: 10px;">
                                            <div style="font-size: 13px;">{{ $product->item_name }}</div>
                                            <small class="text-muted">SKU: {{ $product->item_sku ?: '-' }}</small>
                                        </div>
                                        <div style="width: 280px; margin-right: 10px;">
                                            @if($mapping)
                                                <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 4px; padding: 4px 10px; font-size: 12px;">
                                                    <i class="fas fa-link" style="color: #4CAF50; margin-right: 4px;"></i>
                                                    <strong>{{ $mapping->variation?->sub_sku }}</strong>
                                                    <span class="text-muted"> - {{ $mapping->product?->name }}</span>
                                                </div>
                                            @else
                                                <div style="background: #fff3e0; border: 1px solid #ffe0b2; border-radius: 4px; padding: 4px 10px; font-size: 12px; color: #e65100;">
                                                    <i class="fas fa-unlink" style="margin-right: 4px;"></i>
                                                    @lang('shopee::lang.not_linked')
                                                </div>
                                            @endif
                                        </div>
                                        <div style="width: 60px; text-align: center; margin-right: 10px;">
                                            <span style="font-size: 13px;">{{ $product->stock }}</span>
                                        </div>
                                        <div style="width: 80px; text-align: center;">
                                            @if($mapping)
                                                <button type="button" class="btn btn-xs btn-danger btn-unlink"
                                                        data-shop-id="{{ $product->shopee_shop_id }}"
                                                        data-item-id="{{ $product->item_id }}"
                                                        data-model-id=""
                                                        title="@lang('shopee::lang.unlink')">
                                                    <i class="fas fa-unlink"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-xs btn-success btn-link-product"
                                                        data-shop-id="{{ $product->shopee_shop_id }}"
                                                        data-item-id="{{ $product->item_id }}"
                                                        data-model-id=""
                                                        data-sku="{{ $product->item_sku }}"
                                                        title="@lang('shopee::lang.link')">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Pagination -->
                <div class="box box-solid" style="margin-bottom: 15px;">
                    <div class="box-body" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="text-muted">{{ $products->firstItem() }}-{{ $products->lastItem() }} / {{ $products->total() }} @lang('shopee::lang.products')</div>
                        <div>{{ $products->appends(request()->except('page'))->links() }}</div>
                    </div>
                </div>

            @else
                <div class="box box-solid">
                    <div class="box-body" style="text-align: center; padding: 60px 20px; color: #999;">
                        <i class="fas fa-store" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                        <p style="font-size: 16px;">@lang('shopee::lang.no_shopee_products')</p>
                        @if($shops->count() > 0)
                        <form method="POST" action="{{ route('shopee.product-mappings.sync-products') }}" style="margin-top: 15px;">
                            @csrf
                            @if($shopId)
                                <input type="hidden" name="shop_id" value="{{ $shopId }}">
                            @endif
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-sync-alt"></i> @lang('shopee::lang.sync_products')
                            </button>
                        </form>
                        @else
                        <p><a href="{{ route('shopee.settings') }}" class="btn btn-primary">@lang('shopee::lang.shopee_settings')</a></p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

</section>

<!-- Link Product Modal -->
<div class="modal fade" id="linkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #ee4d2d; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                <h4 class="modal-title"><i class="fas fa-link"></i> @lang('shopee::lang.link_to_pos')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('shopee::lang.search_pos_product')</label>
                    <input type="text" id="linkSearchInput" class="form-control"
                           placeholder="@lang('shopee::lang.search_by_sku_or_name')" autocomplete="off">
                    <div id="linkSearchResults" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-top: none; display: none;"></div>
                </div>
                <input type="hidden" id="linkShopId">
                <input type="hidden" id="linkItemId">
                <input type="hidden" id="linkModelId">
                <input type="hidden" id="linkVariationId">
                <div id="linkSelectedProduct" style="display: none; padding: 10px; background: #e8f5e9; border-radius: 4px; margin-top: 10px;">
                    <i class="fas fa-check-circle" style="color: #4CAF50;"></i>
                    <span id="linkSelectedText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('shopee::lang.cancel')</button>
                <button type="button" class="btn btn-success" id="linkConfirmBtn" disabled>
                    <i class="fas fa-link"></i> @lang('shopee::lang.confirm_link')
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script>
var posVariations = @json($posVariations);

// Open link modal
document.querySelectorAll('.btn-link-product').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('linkShopId').value = this.dataset.shopId;
        document.getElementById('linkItemId').value = this.dataset.itemId;
        document.getElementById('linkModelId').value = this.dataset.modelId;
        document.getElementById('linkVariationId').value = '';
        document.getElementById('linkSelectedProduct').style.display = 'none';
        document.getElementById('linkConfirmBtn').disabled = true;
        document.getElementById('linkSearchInput').value = this.dataset.sku || '';
        document.getElementById('linkSearchResults').style.display = 'none';

        // Auto-search if SKU exists
        if (this.dataset.sku) {
            filterVariations(this.dataset.sku);
        }

        $('#linkModal').modal('show');
    });
});

// Search POS products
var searchTimeout = null;
document.getElementById('linkSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    var q = this.value;
    searchTimeout = setTimeout(function() {
        filterVariations(q);
    }, 300);
});

function filterVariations(query) {
    var results = document.getElementById('linkSearchResults');
    if (!query || query.length < 1) {
        results.style.display = 'none';
        return;
    }

    var q = query.toLowerCase();
    var filtered = posVariations.filter(function(v) {
        return v.label.toLowerCase().indexOf(q) !== -1 || (v.sub_sku && v.sub_sku.toLowerCase().indexOf(q) !== -1);
    }).slice(0, 15);

    if (filtered.length === 0) {
        results.innerHTML = '<div style="padding: 10px; color: #999; text-align: center;">Không tìm thấy sản phẩm</div>';
    } else {
        results.innerHTML = filtered.map(function(v) {
            return '<div class="search-result-item" style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 13px;" ' +
                   'data-variation-id="' + v.id + '" data-label="' + escapeHtml(v.label) + '">' +
                   '<strong>' + (v.sub_sku || '') + '</strong> ' + escapeHtml(v.label) + '</div>';
        }).join('');
    }
    results.style.display = 'block';

    // Click handler for results
    results.querySelectorAll('.search-result-item').forEach(function(item) {
        item.addEventListener('click', function() {
            document.getElementById('linkVariationId').value = this.dataset.variationId;
            document.getElementById('linkSelectedText').textContent = this.dataset.label;
            document.getElementById('linkSelectedProduct').style.display = 'block';
            document.getElementById('linkConfirmBtn').disabled = false;
            results.style.display = 'none';
        });
        item.addEventListener('mouseenter', function() { this.style.background = '#f5f5f5'; });
        item.addEventListener('mouseleave', function() { this.style.background = ''; });
    });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Confirm link
document.getElementById('linkConfirmBtn').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('{{ route("shopee.product-mappings.link") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            shopee_shop_id: document.getElementById('linkShopId').value,
            shopee_item_id: document.getElementById('linkItemId').value,
            shopee_model_id: document.getElementById('linkModelId').value || null,
            variation_id: document.getElementById('linkVariationId').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.msg || 'Error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-link"></i> @lang("shopee::lang.confirm_link")';
        }
    });
});

// Unlink
document.querySelectorAll('.btn-unlink').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('@lang("shopee::lang.confirm_unlink")')) return;

        var el = this;
        fetch('{{ route("shopee.product-mappings.unlink") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                shopee_shop_id: el.dataset.shopId,
                shopee_item_id: el.dataset.itemId,
                shopee_model_id: el.dataset.modelId || null
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { location.reload(); }
        });
    });
});
</script>
@endsection
