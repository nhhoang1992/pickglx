@extends('layouts.app')
@section('title', __('shopee::lang.order_detail') . ' - ' . $order->order_sn)

@section('content')

<!-- Content Header -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-shopping-bag"></i> @lang('shopee::lang.order_detail')
        <small>{{ $order->order_sn }}</small>
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

    <!-- Back button + Actions -->
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-sm-6">
            <a href="{{ route('shopee.orders') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> @lang('shopee::lang.back_to_list')
            </a>
        </div>
        <div class="col-sm-6 text-right">
            @if($order->internal_status === 'new')
                <form method="POST" action="{{ route('shopee.orders.confirm', $order->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> @lang('shopee::lang.confirm_order')
                    </button>
                </form>
            @endif

            @if($order->internal_status === 'confirmed')
                <form method="POST" action="{{ route('shopee.orders.packing', $order->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-box"></i> @lang('shopee::lang.pack_order')
                    </button>
                </form>
            @endif

            @if($order->internal_status === 'packing')
                <form method="POST" action="{{ route('shopee.orders.ready-to-ship', $order->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-truck-loading"></i> @lang('shopee::lang.ready_to_ship_action')
                    </button>
                </form>
            @endif

            @if(in_array($order->internal_status, ['ready_to_ship', 'confirmed']))
                <form method="POST" action="{{ route('shopee.orders.ship', $order->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Xác nhận gửi hàng đơn {{ $order->order_sn }}?')">
                        <i class="fas fa-shipping-fast"></i> @lang('shopee::lang.ship_order')
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Order Info + Products -->
        <div class="col-md-8">

            <!-- Order Status -->
            <div class="box box-solid" style="border-top: 3px solid #ee4d2d;">
                <div class="box-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin: 0; font-size: 20px;">
                                {{ $order->order_sn }}
                                @if($order->is_express)
                                    <span class="label" style="background-color: #ee4d2d; font-size: 12px;">@lang('shopee::lang.express_order')</span>
                                @endif
                            </h3>
                            <p class="text-muted" style="margin: 5px 0 0;">
                                @lang('shopee::lang.order_date'): {{ $order->order_created_at ? $order->order_created_at->format('H:i:s d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                        <div>
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
                            <span class="label" style="background-color: {{ $color }}; font-size: 14px; padding: 6px 14px;">
                                @lang('shopee::lang.status_' . $order->internal_status)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product List -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-list"></i> @lang('shopee::lang.product_list')</h3>
                </div>
                <div class="box-body table-responsive" style="padding: 0;">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 60px;"></th>
                                <th>@lang('shopee::lang.products')</th>
                                <th>@lang('shopee::lang.sku')</th>
                                <th style="text-align: right;">@lang('shopee::lang.price')</th>
                                <th style="text-align: center;">@lang('shopee::lang.quantity')</th>
                                <th style="text-align: right;">@lang('shopee::lang.subtotal')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                                    @else
                                        <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #ccc; font-size: 20px;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ $item->item_name }}</div>
                                    @if($item->model_name)
                                        <small class="text-muted">@lang('shopee::lang.variant'): {{ $item->model_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $item->model_sku ?: $item->item_sku ?: '-' }}</code>
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format($item->discounted_price, 0, ',', '.') }} đ
                                    @if($item->original_price > $item->discounted_price)
                                        <br><small class="text-muted" style="text-decoration: line-through;">{{ number_format($item->original_price, 0, ',', '.') }} đ</small>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right; font-weight: bold;">
                                    {{ number_format($item->discounted_price * $item->quantity, 0, ',', '.') }} đ
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background: #fafafa;">
                            <tr>
                                <td colspan="5" style="text-align: right;">@lang('shopee::lang.total_amount'):</td>
                                <td style="text-align: right; font-weight: bold; color: #ee4d2d; font-size: 16px;">
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                </td>
                            </tr>
                            @if($order->shipping_fee > 0)
                            <tr>
                                <td colspan="5" style="text-align: right;">@lang('shopee::lang.shipping_fee'):</td>
                                <td style="text-align: right;">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</td>
                            </tr>
                            @endif
                            @if($order->shopee_discount > 0)
                            <tr>
                                <td colspan="5" style="text-align: right;">@lang('shopee::lang.shopee_discount'):</td>
                                <td style="text-align: right; color: #4CAF50;">-{{ number_format($order->shopee_discount, 0, ',', '.') }} đ</td>
                            </tr>
                            @endif
                            @if($order->seller_discount > 0)
                            <tr>
                                <td colspan="5" style="text-align: right;">@lang('shopee::lang.seller_discount'):</td>
                                <td style="text-align: right; color: #4CAF50;">-{{ number_format($order->seller_discount, 0, ',', '.') }} đ</td>
                            </tr>
                            @endif
                            @if($order->voucher_discount > 0)
                            <tr>
                                <td colspan="5" style="text-align: right;">@lang('shopee::lang.voucher_discount'):</td>
                                <td style="text-align: right; color: #4CAF50;">-{{ number_format($order->voucher_discount, 0, ',', '.') }} đ</td>
                            </tr>
                            @endif
                            <tr style="border-top: 2px solid #ddd;">
                                <td colspan="5" style="text-align: right; font-weight: bold;">@lang('shopee::lang.buyer_paid'):</td>
                                <td style="text-align: right; font-weight: bold; color: #ee4d2d; font-size: 18px;">
                                    {{ number_format($order->buyer_paid_amount, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Internal Note -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-sticky-note"></i> @lang('shopee::lang.internal_note')</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ route('shopee.orders.note', $order->id) }}">
                        @csrf
                        <div class="form-group">
                            <textarea name="note" class="form-control" rows="3" placeholder="@lang('shopee::lang.internal_note')...">{{ $order->note }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> @lang('shopee::lang.save_note')
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Buyer Info + Shipping + Timeline -->
        <div class="col-md-4">

            <!-- Buyer Info -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-user"></i> @lang('shopee::lang.buyer_info')</h3>
                </div>
                <div class="box-body">
                    <table class="table table-condensed" style="margin-bottom: 0;">
                        @if($order->buyer_username)
                        <tr>
                            <td class="text-muted" style="width: 40%;"><i class="fas fa-user"></i> Username</td>
                            <td>{{ $order->buyer_username }}</td>
                        </tr>
                        @endif
                        @if($order->buyer_name)
                        <tr>
                            <td class="text-muted"><i class="fas fa-id-card"></i> @lang('shopee::lang.buyer_name')</td>
                            <td><strong>{{ $order->buyer_name }}</strong></td>
                        </tr>
                        @endif
                        @if($order->buyer_phone)
                        <tr>
                            <td class="text-muted"><i class="fas fa-phone"></i> @lang('shopee::lang.buyer_phone')</td>
                            <td>{{ $order->buyer_phone }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-truck"></i> @lang('shopee::lang.shipping_address')</h3>
                </div>
                <div class="box-body">
                    @if($order->shipping_address)
                        <p>{{ $order->shipping_address }}</p>
                    @endif

                    <table class="table table-condensed" style="margin-bottom: 0;">
                        @if($order->shipping_carrier)
                        <tr>
                            <td class="text-muted">@lang('shopee::lang.shipping_carrier')</td>
                            <td>{{ $order->shipping_carrier }}</td>
                        </tr>
                        @endif
                        @if($order->tracking_number)
                        <tr>
                            <td class="text-muted">@lang('shopee::lang.tracking_number')</td>
                            <td><code>{{ $order->tracking_number }}</code></td>
                        </tr>
                        @endif
                        @if($order->payment_method)
                        <tr>
                            <td class="text-muted">@lang('shopee::lang.payment_method')</td>
                            <td>{{ $order->payment_method }}</td>
                        </tr>
                        @endif
                        @if($order->ship_by_date)
                        <tr>
                            <td class="text-muted">@lang('shopee::lang.ship_by_date')</td>
                            <td>
                                <span style="color: {{ $order->ship_by_date->isPast() ? '#ee4d2d' : '#4CAF50' }}; font-weight: bold;">
                                    {{ $order->ship_by_date->format('H:i d/m/Y') }}
                                </span>
                            </td>
                        </tr>
                        @endif
                    </table>

                    @if($order->message_to_seller)
                    <div style="margin-top: 10px; padding: 10px; background: #fff3e0; border-radius: 4px; border-left: 3px solid #FF9800;">
                        <small class="text-muted"><i class="fas fa-comment"></i> @lang('shopee::lang.message_to_seller'):</small>
                        <p style="margin: 5px 0 0;">{{ $order->message_to_seller }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-history"></i> @lang('shopee::lang.order_timeline')</h3>
                </div>
                <div class="box-body">
                    <ul class="timeline" style="list-style: none; padding: 0; margin: 0;">
                        @php
                            $timelineItems = [
                                ['time' => $order->order_created_at, 'label' => __('shopee::lang.created_at'), 'icon' => 'fas fa-plus-circle', 'color' => '#2196F3', 'user' => null],
                                ['time' => $order->confirmed_at, 'label' => __('shopee::lang.confirmed_at'), 'icon' => 'fas fa-check-circle', 'color' => '#4CAF50', 'user' => $order->confirmedBy],
                                ['time' => $order->packed_at, 'label' => __('shopee::lang.packed_at'), 'icon' => 'fas fa-box', 'color' => '#FF9800', 'user' => $order->packedBy],
                                ['time' => $order->shipped_at, 'label' => __('shopee::lang.shipped_at'), 'icon' => 'fas fa-shipping-fast', 'color' => '#00BCD4', 'user' => $order->shippedBy],
                            ];
                        @endphp
                        @foreach($timelineItems as $item)
                            <li style="display: flex; align-items: flex-start; margin-bottom: 15px; {{ !$item['time'] ? 'opacity: 0.4;' : '' }}">
                                <div style="width: 30px; text-align: center; margin-right: 10px;">
                                    <i class="{{ $item['icon'] }}" style="color: {{ $item['color'] }}; font-size: 16px;"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 500;">{{ $item['label'] }}</div>
                                    @if($item['time'])
                                        <small class="text-muted">{{ $item['time']->format('H:i:s d/m/Y') }}</small>
                                        @if($item['user'])
                                            <br><small class="text-muted">{{ $item['user']->first_name }} {{ $item['user']->last_name }}</small>
                                        @endif
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Shop Info -->
            @if($order->shop)
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-store"></i> @lang('shopee::lang.shop_name')</h3>
                </div>
                <div class="box-body">
                    <p><strong>{{ $order->shop->shop_name }}</strong></p>
                    <small class="text-muted">ID: {{ $order->shop->shop_id }}</small>
                </div>
            </div>
            @endif

        </div>
    </div>

</section>

@endsection
