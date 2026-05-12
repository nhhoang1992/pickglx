@extends('layouts.app')
@section('title', __('shopee::lang.product_mapping'))

@section('content')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-link"></i> @lang('shopee::lang.product_mapping')
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

    <!-- Info Box -->
    <div class="row">
        <div class="col-sm-12">
            <div class="callout callout-info">
                <h4><i class="fas fa-info-circle"></i> @lang('shopee::lang.mapping_info_title')</h4>
                <p>@lang('shopee::lang.mapping_info_desc')</p>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body">
                    <form method="GET" action="{{ route('shopee.product-mappings') }}" class="form-inline" style="display: inline;">
                        <div class="form-group" style="margin-right: 10px;">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control" style="min-width: 250px;"
                                       placeholder="@lang('shopee::lang.search_mapping_placeholder')"
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        @if($shops->count() > 1)
                        <div class="form-group" style="margin-right: 10px;">
                            <select name="shop_id" class="form-control">
                                <option value="">@lang('shopee::lang.all_shops')</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" {{ ($shopId ?? '') == $shop->id ? 'selected' : '' }}>
                                        {{ $shop->shop_name ?: $shop->shop_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> @lang('shopee::lang.filter')
                        </button>
                    </form>

                    <div class="pull-right">
                        <form method="POST" action="{{ route('shopee.product-mappings.auto-match') }}" style="display: inline;">
                            @csrf
                            @if($shopId ?? null)
                                <input type="hidden" name="shop_id" value="{{ $shopId }}">
                            @endif
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('@lang('shopee::lang.confirm_auto_match')')">
                                <i class="fas fa-magic"></i> @lang('shopee::lang.auto_match_sku')
                            </button>
                        </form>

                        <a href="{{ route('shopee.product-mappings.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> @lang('shopee::lang.add_mapping')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mappings Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body table-responsive" style="padding: 0;">
                    @if($mappings->count() > 0)
                    <table class="table table-hover table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 50px;"></th>
                                <th>@lang('shopee::lang.shopee_product')</th>
                                <th style="text-align: center;"><i class="fas fa-arrows-alt-h"></i></th>
                                <th>@lang('shopee::lang.pos_product')</th>
                                <th>@lang('shopee::lang.shop_name')</th>
                                <th style="text-align: center;">@lang('shopee::lang.auto_destock_label')</th>
                                <th>@lang('shopee::lang.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mappings as $mapping)
                            <tr>
                                <td>
                                    @if($mapping->shopee_image_url)
                                        <img src="{{ $mapping->shopee_image_url }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                                    @else
                                        <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #ccc;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ $mapping->shopee_item_name }}</div>
                                    @if($mapping->shopee_model_name)
                                        <small class="text-muted">{{ $mapping->shopee_model_name }}</small><br>
                                    @endif
                                    @if($mapping->shopee_sku)
                                        <code style="font-size: 11px;">{{ $mapping->shopee_sku }}</code>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <i class="fas fa-link" style="color: #4CAF50; font-size: 18px;"></i>
                                </td>
                                <td>
                                    @if($mapping->product)
                                        <div style="font-weight: 500;">{{ $mapping->product->name }}</div>
                                        @if($mapping->variation)
                                            <small class="text-muted">{{ $mapping->variation->name !== 'DUMMY' ? $mapping->variation->name : '' }}</small>
                                            @if($mapping->variation->sub_sku)
                                                <br><code style="font-size: 11px;">{{ $mapping->variation->sub_sku }}</code>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Sản phẩm đã bị xóa</span>
                                    @endif
                                </td>
                                <td>
                                    @if($mapping->shop)
                                        {{ $mapping->shop->shop_name }}
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($mapping->auto_destock)
                                        <span class="label label-success"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="label label-default"><i class="fas fa-times"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('shopee.product-mappings.edit', $mapping->id) }}" class="btn btn-xs btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('shopee.product-mappings.destroy', $mapping->id) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"
                                                onclick="return confirm('@lang('shopee::lang.confirm_delete_mapping')')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="text-align: center; padding: 60px 20px; color: #999;">
                        <i class="fas fa-link" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                        <p style="font-size: 16px;">@lang('shopee::lang.no_mappings')</p>
                        <a href="{{ route('shopee.product-mappings.create') }}" class="btn btn-primary" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> @lang('shopee::lang.add_mapping')
                        </a>
                    </div>
                    @endif
                </div>

                @if($mappings->count() > 0)
                <div class="box-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-muted">
                        {{ $mappings->firstItem() }}-{{ $mappings->lastItem() }} / {{ $mappings->total() }}
                    </div>
                    <div>{{ $mappings->appends(request()->except('page'))->links() }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection
