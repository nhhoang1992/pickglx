@extends('layouts.app')
@section('title', __('shopee::lang.add_mapping'))

@section('content')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-link"></i> @lang('shopee::lang.add_mapping')
    </h1>
</section>

<section class="content">

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-solid" style="border-top: 3px solid #ee4d2d;">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('shopee::lang.link_shopee_to_pos')</h3>
                </div>
                <form method="POST" action="{{ route('shopee.product-mappings.store') }}">
                    @csrf
                    <div class="box-body">

                        <!-- Shopee Shop -->
                        <div class="form-group">
                            <label>@lang('shopee::lang.shop_name') <span class="text-danger">*</span></label>
                            <select name="shopee_shop_id" class="form-control" required>
                                <option value="">@lang('shopee::lang.select_shop')</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}">{{ $shop->shop_name ?: $shop->shop_id }}</option>
                                @endforeach
                            </select>
                        </div>

                        <hr>
                        <h4><i class="fas fa-store" style="color: #ee4d2d;"></i> @lang('shopee::lang.shopee_product_info')</h4>

                        <!-- Shopee Item ID -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shopee Item ID <span class="text-danger">*</span></label>
                                    <input type="number" name="shopee_item_id" class="form-control" required
                                           placeholder="Ví dụ: 12345678">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shopee Model ID</label>
                                    <input type="text" name="shopee_model_id" class="form-control"
                                           placeholder="Để trống nếu không có biến thể">
                                </div>
                            </div>
                        </div>

                        <!-- Shopee Product Info -->
                        <div class="form-group">
                            <label>@lang('shopee::lang.shopee_product_name') <span class="text-danger">*</span></label>
                            <input type="text" name="shopee_item_name" class="form-control" required
                                   placeholder="Tên sản phẩm trên Shopee">
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>SKU Shopee</label>
                                    <input type="text" name="shopee_sku" class="form-control"
                                           placeholder="Mã SKU trên Shopee">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('shopee::lang.variant')</label>
                                    <input type="text" name="shopee_model_name" class="form-control"
                                           placeholder="Ví dụ: Đỏ - XL">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>URL Ảnh</label>
                                    <input type="text" name="shopee_image_url" class="form-control"
                                           placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4><i class="fas fa-box" style="color: #337ab7;"></i> @lang('shopee::lang.pos_product_info')</h4>

                        <!-- UltimatePOS Product -->
                        <div class="form-group">
                            <label>@lang('shopee::lang.pos_product') <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-control select2" required>
                                <option value="">@lang('shopee::lang.select_product')</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} (SKU: {{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('shopee::lang.pos_variation') <span class="text-danger">*</span></label>
                            <select name="variation_id" id="variation_id" class="form-control" required>
                                <option value="">@lang('shopee::lang.select_variation')</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Auto Destock -->
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="auto_destock" value="1" checked>
                                    <strong>@lang('shopee::lang.auto_destock_label')</strong>
                                    <br><small class="text-muted">@lang('shopee::lang.auto_destock_desc')</small>
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
