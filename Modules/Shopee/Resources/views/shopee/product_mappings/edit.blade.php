@extends('layouts.app')
@section('title', __('shopee::lang.edit_mapping'))

@section('content')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-link"></i> @lang('shopee::lang.edit_mapping')
    </h1>
</section>

<section class="content">

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-solid" style="border-top: 3px solid #ee4d2d;">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('shopee::lang.edit_mapping')</h3>
                </div>
                <form method="POST" action="{{ route('shopee.product-mappings.update', $mapping->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="box-body">

                        <!-- Shopee Info (read-only) -->
                        <h4><i class="fas fa-store" style="color: #ee4d2d;"></i> @lang('shopee::lang.shopee_product_info')</h4>
                        <table class="table table-condensed">
                            <tr>
                                <td class="text-muted" style="width: 30%;">@lang('shopee::lang.shop_name')</td>
                                <td>{{ $mapping->shop->shop_name ?? $mapping->shopee_shop_id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">@lang('shopee::lang.shopee_product_name')</td>
                                <td>
                                    <strong>{{ $mapping->shopee_item_name }}</strong>
                                    @if($mapping->shopee_model_name)
                                        <br><small>{{ $mapping->shopee_model_name }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">SKU Shopee</td>
                                <td><code>{{ $mapping->shopee_sku ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Item ID</td>
                                <td>{{ $mapping->shopee_item_id }} {{ $mapping->shopee_model_id ? '/ Model: ' . $mapping->shopee_model_id : '' }}</td>
                            </tr>
                        </table>

                        <hr>
                        <h4><i class="fas fa-box" style="color: #337ab7;"></i> @lang('shopee::lang.pos_product_info')</h4>

                        <!-- UltimatePOS Product -->
                        <div class="form-group">
                            <label>@lang('shopee::lang.pos_product') <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-control select2" required>
                                <option value="">@lang('shopee::lang.select_product')</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $mapping->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} (SKU: {{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('shopee::lang.pos_variation') <span class="text-danger">*</span></label>
                            <select name="variation_id" id="variation_id" class="form-control" required>
                                <option value="">@lang('shopee::lang.select_variation')</option>
                                @if($mapping->product)
                                    @foreach($mapping->product->variations as $v)
                                        <option value="{{ $v->id }}" {{ $mapping->variation_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->name === 'DUMMY' ? 'Mặc định' : $v->name }}
                                            {{ $v->sub_sku ? '(SKU: ' . $v->sub_sku . ')' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <hr>

                        <!-- Auto Destock -->
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="auto_destock" value="1" {{ $mapping->auto_destock ? 'checked' : '' }}>
                                    <strong>@lang('shopee::lang.auto_destock_label')</strong>
                                    <br><small class="text-muted">@lang('shopee::lang.auto_destock_desc')</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" {{ $mapping->is_active ? 'checked' : '' }}>
                                    <strong>@lang('shopee::lang.mapping_active')</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('shopee.product-mappings') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> @lang('shopee::lang.back_to_list')
                        </a>
                        <button type="submit" class="btn btn-primary pull-right">
                            <i class="fas fa-save"></i> @lang('shopee::lang.save_mapping')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</section>
@endsection

@section('javascript')
<script>
document.getElementById('product_id').addEventListener('change', function() {
    var productId = this.value;
    var variationSelect = document.getElementById('variation_id');
    variationSelect.innerHTML = '<option value="">@lang("shopee::lang.select_variation")</option>';

    if (!productId) return;

    fetch('{{ route("shopee.product-mappings.variations") }}?product_id=' + productId)
        .then(response => response.json())
        .then(variations => {
            variations.forEach(function(v) {
                var opt = document.createElement('option');
                opt.value = v.id;
                var name = v.name === 'DUMMY' ? 'Mặc định' : v.name;
                opt.textContent = name + (v.sub_sku ? ' (SKU: ' + v.sub_sku + ')' : '') + ' - ' + parseFloat(v.default_sell_price).toLocaleString() + 'đ';
                variationSelect.appendChild(opt);
            });
        });
});
</script>
@endsection
