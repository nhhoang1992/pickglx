@extends('layouts.app')
@section('title', __('shopee::lang.order_list'))

@section('content')

<!-- Content Header -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-shopping-bag"></i> @lang('shopee::lang.order_list')
    </h1>
</section>

<!-- Main content -->
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

    <!-- Status Tabs -->
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid" style="border-top: 3px solid #ee4d2d;">
                <div class="box-body" style="padding: 0;">
                    <ul class="nav nav-tabs" style="border-bottom: 2px solid #f4f4f4; padding: 0 10px; flex-wrap: nowrap; overflow-x: auto; white-space: nowrap;">
                        @php
                            $statusTabs = [
                                'all' => ['label' => __('shopee::lang.all'), 'color' => '#333'],
                                'new' => ['label' => __('shopee::lang.status_new'), 'color' => '#ee4d2d'],
                                'confirmed' => ['label' => __('shopee::lang.status_confirmed'), 'color' => '#2196F3'],
                                'packing' => ['label' => __('shopee::lang.status_packing'), 'color' => '#FF9800'],
                                'ready_to_ship' => ['label' => __('shopee::lang.status_ready_to_ship'), 'color' => '#9C27B0'],
                                'shipped' => ['label' => __('shopee::lang.status_shipped'), 'color' => '#00BCD4'],
                                'delivering' => ['label' => __('shopee::lang.status_delivering'), 'color' => '#4CAF50'],
                                'completed' => ['label' => __('shopee::lang.status_completed'), 'color' => '#4CAF50'],
                                'returned' => ['label' => __('shopee::lang.status_returned'), 'color' => '#FF5722'],
                                'cancelled' => ['label' => __('shopee::lang.status_cancelled'), 'color' => '#9E9E9E'],
                            ];
                        @endphp
                        @foreach($statusTabs as $key => $tab)
                            <li class="{{ $status === $key ? 'active' : '' }}" style="display: inline-block; float: none;">
                                <a href="{{ route('shopee.orders', array_merge(request()->except('status', 'page'), ['status' => $key])) }}"
                                   style="padding: 12px 15px; {{ $status === $key ? 'border-bottom: 3px solid ' . $tab['color'] . '; color: ' . $tab['color'] . '; font-weight: bold;' : 'color: #666;' }}">
                                    {{ $tab['label'] }}
                                    @if(($statusCounts[$key] ?? 0) > 0)
                                        <span class="badge" style="background-color: {{ $tab['color'] }}; color: #fff; font-size: 11px; padding: 2px 6px; border-radius: 10px;">
                                            {{ $statusCounts[$key] }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions Bar -->
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body">
                    <form method="GET" action="{{ route('shopee.orders') }}" class="form-inline">
                        <input type="hidden" name="status" value="{{ $status }}">

                        <!-- Search -->
                        <div class="form-group" style="margin-right: 10px; margin-bottom: 5px;">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control" style="min-width: 280px;"
                                       placeholder="@lang('shopee::lang.search_placeholder')"
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        <!-- Shop Filter -->
                        @if($shops->count() > 1)
                        <div class="form-group" style="margin-right: 10px; margin-bottom: 5px;">
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

                        <!-- Date Range -->
                        <div class="form-group" style="margin-right: 10px; margin-bottom: 5px;">
                            <input type="date" name="date_from" class="form-control" placeholder="@lang('shopee::lang.date_from')"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="form-group" style="margin-right: 10px; margin-bottom: 5px;">
                            <input type="date" name="date_to" class="form-control" placeholder="@lang('shopee::lang.date_to')"
                                   value="{{ request('date_to') }}">
                        </div>

                        <!-- Express filter -->
                        <div class="form-group" style="margin-right: 10px; margin-bottom: 5px;">
                            <label style="font-weight: normal;">
                                <input type="checkbox" name="is_express" value="1" {{ request('is_express') ? 'checked' : '' }}>
                                <span style="color: #ee4d2d; font-weight: bold;">@lang('shopee::lang.filter_express')</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-bottom: 5px;">
                            <i class="fas fa-filter"></i> @lang('shopee::lang.filter')
                        </button>
                    </form>

                    <div class="pull-right" style="margin-top: -35px;">
                        <!-- Sync Orders -->
                        <form method="POST" action="{{ route('shopee.orders.sync') }}" style="display: inline;">
                            @csrf
                            @if($shopId)
                                <input type="hidden" name="shop_id" value="{{ $shopId }}">
                            @endif
                            <button type="submit" class="btn btn-success" onclick="return confirm('Đồng bộ đơn hàng từ Shopee?')">
                                <i class="fas fa-sync-alt"></i> @lang('shopee::lang.sync_orders')
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($orders->count() > 0)
    <form id="bulkActionForm" method="POST" action="{{ route('shopee.orders.bulk-action') }}">
        @csrf
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-sm-12">
                <div class="form-inline">
                    <div class="form-group" style="margin-right: 10px;">
                        <select name="action" class="form-control" id="bulkActionSelect">
                            <option value="">@lang('shopee::lang.select_action')</option>
                            <option value="confirm">@lang('shopee::lang.confirm_order')</option>
                            <option value="packing">@lang('shopee::lang.pack_order')</option>
                            <option value="ready_to_ship">@lang('shopee::lang.ready_to_ship_action')</option>
                            <option value="ship">@lang('shopee::lang.ship_order')</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-default" onclick="return validateBulkAction()">
                        <i class="fas fa-check"></i> @lang('shopee::lang.apply')
                    </button>
                    <span id="selectedCount" style="margin-left: 10px; color: #666;"></span>
                </div>
            </div>
        </div>
    @endif

    <!-- Orders Table -->
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body table-responsive" style="padding: 0;">
                    @if($orders->count() > 0)
                    <table class="table table-hover table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 30px; padding: 10px;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th style="min-width: 180px;">@lang('shopee::lang.order_sn')</th>
                                <th>@lang('shopee::lang.order_date')</th>
                                <th>@lang('shopee::lang.products')</th>
                                <th style="text-align: right;">@lang('shopee::lang.total_amount')</th>
                                <th>@lang('shopee::lang.status')</th>
                                <th>@lang('shopee::lang.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td style="padding: 10px; vertical-align: top;">
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox" form="bulkActionForm">
                                </td>
                                <td style="vertical-align: top; padding-top: 12px;">
                                    <a href="{{ route('shopee.orders.show', $order->id) }}" style="font-weight: bold; color: #337ab7;">
                                        {{ $order->order_sn }}
                                    </a>
                                    <br>
                                    @if($order->is_express)
                                        <span class="label" style="background-color: #ee4d2d;">@lang('shopee::lang.express_order')</span>
                                    @endif
                                    @if($order->tracking_number)
                                        <br><small class="text-muted"><i class="fas fa-truck"></i> {{ $order->tracking_number }}</small>
                                    @endif
                                    @if($shops->count() > 1 && $order->shop)
                                        <br><small class="text-muted"><i class="fas fa-store"></i> {{ $order->shop->shop_name }}</small>
                                    @endif
                                </td>
                                <td style="vertical-align: top; padding-top: 12px;">
                                    <span style="white-space: nowrap;">{{ $order->order_created_at ? $order->order_created_at->format('H:i:s') : '' }}</span>
                                    <br>
                                    <small class="text-muted">{{ $order->order_created_at ? $order->order_created_at->format('d/m/Y') : '' }}</small>
                                </td>
                                <td style="vertical-align: top; padding-top: 8px; max-width: 400px;">
                                    @foreach($order->items->take(3) as $item)
                                    <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                        @if($item->image_url)
                                            <img src="{{ $item->image_url }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 8px; border: 1px solid #eee;">
                                        @else
                                            <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; margin-right: 8px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: #ccc;"></i>
                                            </div>
                                        @endif
                                        <div style="flex: 1; overflow: hidden;">
                                            <div style="font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 300px;">
                                                {{ $item->item_name }}
                                            </div>
                                            @if($item->model_name)
                                                <small class="text-muted">{{ $item->model_name }}</small>
                                            @endif
                                            @if($item->item_sku || $item->model_sku)
                                                <small class="text-muted">SKU: {{ $item->model_sku ?: $item->item_sku }}</small>
                                            @endif
                                        </div>
                                        <div style="text-align: right; margin-left: 10px; white-space: nowrap;">
                                            x{{ $item->quantity }}
                                        </div>
                                    </div>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <small class="text-muted">+{{ $order->items->count() - 3 }} @lang('shopee::lang.products')...</small>
                                    @endif
                                </td>
                                <td style="vertical-align: top; padding-top: 12px; text-align: right; white-space: nowrap;">
                                    <span style="font-weight: bold; color: #ee4d2d;">
                                        {{ number_format($order->total_amount, 0, ',', '.') }} {{ $order->currency === 'VND' ? 'đ' : $order->currency }}
                                    </span>
                                </td>
                                <td style="vertical-align: top; padding-top: 12px;">
                                    @php
                                        $statusColors = [
                                            'new' => '#ee4d2d',
                                            'confirmed' => '#2196F3',
                                            'packing' => '#FF9800',
                                            'ready_to_ship' => '#9C27B0',
                                            'shipped' => '#00BCD4',
                                            'delivering' => '#4CAF50',
                                            'completed' => '#4CAF50',
                                            'returned' => '#FF5722',
                                            'cancelled' => '#9E9E9E',
                                        ];
                                        $color = $statusColors[$order->internal_status] ?? '#666';
                                    @endphp
                                    <span class="label" style="background-color: {{ $color }}; font-size: 12px; padding: 4px 8px;">
                                        @lang('shopee::lang.status_' . $order->internal_status)
                                    </span>
                                </td>
                                <td style="vertical-align: top; padding-top: 10px; white-space: nowrap;">
                                    <a href="{{ route('shopee.orders.show', $order->id) }}" class="btn btn-xs btn-info" title="@lang('shopee::lang.view_detail')">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($order->internal_status === 'new')
                                        <form method="POST" action="{{ route('shopee.orders.confirm', $order->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-primary" title="@lang('shopee::lang.confirm_order')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($order->internal_status === 'confirmed')
                                        <form method="POST" action="{{ route('shopee.orders.packing', $order->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-warning" title="@lang('shopee::lang.pack_order')">
                                                <i class="fas fa-box"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($order->internal_status === 'packing')
                                        <form method="POST" action="{{ route('shopee.orders.ready-to-ship', $order->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success" title="@lang('shopee::lang.ready_to_ship_action')">
                                                <i class="fas fa-truck-loading"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($order->internal_status, ['ready_to_ship', 'confirmed']))
                                        <form method="POST" action="{{ route('shopee.orders.ship', $order->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success"
                                                    title="@lang('shopee::lang.ship_order')"
                                                    onclick="return confirm('Xác nhận gửi hàng đơn {{ $order->order_sn }}?')">
                                                <i class="fas fa-shipping-fast"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="text-align: center; padding: 60px 20px; color: #999;">
                        <i class="fas fa-shopping-bag" style="font-size: 48px; margin-bottom: 15px; color: #ddd;"></i>
                        <p style="font-size: 16px;">@lang('shopee::lang.no_orders')</p>
                        @if($shops->count() > 0)
                        <form method="POST" action="{{ route('shopee.orders.sync') }}" style="margin-top: 15px;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-sync-alt"></i> @lang('shopee::lang.sync_orders')
                            </button>
                        </form>
                        @else
                        <p><a href="{{ route('shopee.settings') }}" class="btn btn-primary">@lang('shopee::lang.shopee_settings')</a></p>
                        @endif
                    </div>
                    @endif
                </div>

                @if($orders->count() > 0)
                <div class="box-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="text-muted">
                        Hiện thị {{ $orders->firstItem() }}-{{ $orders->lastItem() }} trên {{ $orders->total() }} đơn hàng
                    </div>
                    <div>
                        {{ $orders->appends(request()->except('page'))->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($orders->count() > 0)
    </form>
    @endif

</section>

@endsection

@section('javascript')
<script>
    // Select all checkboxes
    document.getElementById('selectAll')?.addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(function(cb) { cb.checked = this.checked; }.bind(this));
        updateSelectedCount();
    });

    // Update selected count
    document.querySelectorAll('.order-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        var count = document.querySelectorAll('.order-checkbox:checked').length;
        var el = document.getElementById('selectedCount');
        if (el) {
            el.textContent = count > 0 ? 'Đã chọn ' + count + ' đơn hàng' : '';
        }
    }

    function validateBulkAction() {
        var action = document.getElementById('bulkActionSelect').value;
        var checked = document.querySelectorAll('.order-checkbox:checked').length;
        if (!action) {
            alert('@lang("shopee::lang.select_action")');
            return false;
        }
        if (checked === 0) {
            alert('@lang("shopee::lang.no_orders_selected")');
            return false;
        }
        return confirm('Xác nhận thực hiện hành động cho ' + checked + ' đơn hàng?');
    }
</script>
@endsection
