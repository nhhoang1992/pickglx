@extends('layouts.app')
@section('title', __('shopee::lang.report_top_products'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-trophy"></i> @lang('shopee::lang.report_top_products')
    </h1>
</section>

<section class="content">
    @include('shopee::shopee.reports._nav')
    @include('shopee::shopee.reports._filter', ['action' => route('shopee.reports.top-products')])

    <!-- Sort Options -->
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-md-12">
            <div class="btn-group">
                <a href="{{ route('shopee.reports.top-products', array_merge(request()->all(), ['sort_by' => 'quantity'])) }}"
                   class="btn btn-sm {{ $sortBy == 'quantity' ? 'btn-primary' : 'btn-default' }}">
                    <i class="fas fa-sort-amount-down"></i> @lang('shopee::lang.sort_by_quantity')
                </a>
                <a href="{{ route('shopee.reports.top-products', array_merge(request()->all(), ['sort_by' => 'revenue'])) }}"
                   class="btn btn-sm {{ $sortBy == 'revenue' ? 'btn-primary' : 'btn-default' }}">
                    <i class="fas fa-sort-amount-down"></i> @lang('shopee::lang.sort_by_revenue')
                </a>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box" style="border-left: 4px solid #ee4d2d;">
                <span class="info-box-icon bg-red"><i class="fas fa-box"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_products_sold')</span>
                    <span class="info-box-number">{{ number_format($totalQuantity) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box" style="border-left: 4px solid #00b894;">
                <span class="info-box-icon bg-green"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_product_revenue')</span>
                    <span class="info-box-number">{{ number_format($totalRevenue, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box" style="border-left: 4px solid #0984e3;">
                <span class="info-box-icon bg-blue"><i class="fas fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.unique_products')</span>
                    <span class="info-box-number">{{ number_format($topProducts->count()) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs: Products / Variants -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-products" data-toggle="tab"><i class="fas fa-box"></i> @lang('shopee::lang.by_product')</a></li>
            <li><a href="#tab-variants" data-toggle="tab"><i class="fas fa-cubes"></i> @lang('shopee::lang.by_variant')</a></li>
        </ul>
        <div class="tab-content">
            <!-- Products Tab -->
            <div class="tab-pane active" id="tab-products">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>@lang('shopee::lang.product_name')</th>
                                <th>SKU</th>
                                <th class="text-right">@lang('shopee::lang.quantity_sold')</th>
                                <th class="text-right">@lang('shopee::lang.revenue')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th>@lang('shopee::lang.percentage')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $index => $product)
                            <tr>
                                <td>
                                    @if($index < 3)
                                        <span class="badge" style="background: {{ ['#ffd700','#c0c0c0','#cd7f32'][$index] }};">{{ $index + 1 }}</span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 8px;">
                                        @endif
                                        <span>{{ \Illuminate\Support\Str::limit($product->item_name, 50) }}</span>
                                    </div>
                                </td>
                                <td><code>{{ $product->item_sku ?: '-' }}</code></td>
                                <td class="text-right"><strong>{{ number_format($product->total_qty) }}</strong></td>
                                <td class="text-right">{{ number_format($product->total_revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($product->order_count) }}</td>
                                <td>
                                    @php $pct = $totalRevenue > 0 ? ($product->total_revenue / $totalRevenue) * 100 : 0; @endphp
                                    <div class="progress progress-sm" style="margin-bottom: 0;">
                                        <div class="progress-bar bg-red" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small>{{ number_format($pct, 1) }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Variants Tab -->
            <div class="tab-pane" id="tab-variants">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>@lang('shopee::lang.product_name')</th>
                                <th>@lang('shopee::lang.variant')</th>
                                <th>SKU</th>
                                <th class="text-right">@lang('shopee::lang.quantity_sold')</th>
                                <th class="text-right">@lang('shopee::lang.revenue')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topVariants as $index => $variant)
                            <tr>
                                <td>
                                    @if($index < 3)
                                        <span class="badge" style="background: {{ ['#ffd700','#c0c0c0','#cd7f32'][$index] }};">{{ $index + 1 }}</span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($variant->item_name, 40) }}</td>
                                <td><span class="label label-info">{{ $variant->model_name ?: '-' }}</span></td>
                                <td><code>{{ $variant->model_sku ?: '-' }}</code></td>
                                <td class="text-right"><strong>{{ number_format($variant->total_qty) }}</strong></td>
                                <td class="text-right">{{ number_format($variant->total_revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($variant->order_count) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
