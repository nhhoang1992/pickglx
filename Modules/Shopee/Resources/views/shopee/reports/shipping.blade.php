@extends('layouts.app')
@section('title', __('shopee::lang.report_shipping'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-truck"></i> @lang('shopee::lang.report_shipping')
    </h1>
</section>

<section class="content">
    @include('shopee::shopee.reports._nav')
    @include('shopee::shopee.reports._filter', ['action' => route('shopee.reports.shipping')])

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #ee4d2d;">
                <span class="info-box-icon bg-red"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_shipping_fee')</span>
                    <span class="info-box-number">{{ number_format($totalShippingFee, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #0984e3;">
                <span class="info-box-icon bg-blue"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.avg_shipping_fee')</span>
                    <span class="info-box-number">{{ number_format($avgShippingFee, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #fdcb6e;">
                <span class="info-box-icon bg-yellow"><i class="fas fa-bolt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.express_orders')</span>
                    <span class="info-box-number">{{ number_format($expressCount) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #00b894;">
                <span class="info-box-icon bg-green"><i class="fas fa-shipping-fast"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.normal_orders')</span>
                    <span class="info-box-number">{{ number_format($normalCount) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Carrier Stats -->
        <div class="col-md-7">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-truck-loading"></i> @lang('shopee::lang.carrier_stats')</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.shipping_carrier')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th class="text-right">@lang('shopee::lang.total_shipping_fee')</th>
                                <th class="text-right">@lang('shopee::lang.avg_shipping_fee')</th>
                                <th class="text-right">@lang('shopee::lang.success_rate')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carrierStats as $carrier)
                            <tr>
                                <td><strong>{{ $carrier->shipping_carrier }}</strong></td>
                                <td class="text-right">{{ number_format($carrier->order_count) }}</td>
                                <td class="text-right">{{ number_format($carrier->total_shipping_fee, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($carrier->avg_shipping_fee, 0, ',', '.') }} đ</td>
                                <td class="text-right">
                                    @php
                                        $delivered = $carrier->completed;
                                        $totalCarrier = $carrier->order_count - $carrier->cancelled;
                                        $successRate = $totalCarrier > 0 ? ($delivered / $totalCarrier) * 100 : 0;
                                    @endphp
                                    <span class="label {{ $successRate >= 90 ? 'label-success' : ($successRate >= 70 ? 'label-warning' : 'label-danger') }}">
                                        {{ number_format($successRate, 1) }}%
                                    </span>
                                    <br><small class="text-muted">
                                        @lang('shopee::lang.returned'): {{ $carrier->returned }}
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Carrier Pie Chart -->
        <div class="col-md-5">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-pie"></i> @lang('shopee::lang.carrier_distribution')</h3>
                </div>
                <div class="box-body">
                    <canvas id="carrierChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping by Region -->
    @if($shippingByCity->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-map-marker-alt"></i> @lang('shopee::lang.shipping_by_region')</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('shopee::lang.city')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th class="text-right">@lang('shopee::lang.revenue')</th>
                                <th class="text-right">@lang('shopee::lang.avg_shipping_fee')</th>
                                <th>@lang('shopee::lang.percentage')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalCityOrders = $shippingByCity->sum('order_count'); @endphp
                            @foreach($shippingByCity as $index => $city)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><i class="fas fa-map-pin" style="color: #ee4d2d;"></i> {{ $city->shipping_city }}</td>
                                <td class="text-right">{{ number_format($city->order_count) }}</td>
                                <td class="text-right">{{ number_format($city->revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($city->avg_shipping, 0, ',', '.') }} đ</td>
                                <td>
                                    @php $pct = $totalCityOrders > 0 ? ($city->order_count / $totalCityOrders) * 100 : 0; @endphp
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
        </div>
    </div>
    @endif
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    var carrierColors = ['#ee4d2d', '#0984e3', '#00b894', '#fdcb6e', '#6c5ce7', '#e17055', '#00cec9', '#636e72'];
    new Chart(document.getElementById('carrierChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($carrierStats->pluck('shipping_carrier')) !!},
            datasets: [{
                data: {!! json_encode($carrierStats->pluck('order_count')) !!},
                backgroundColor: carrierColors.slice(0, {{ $carrierStats->count() }})
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection
