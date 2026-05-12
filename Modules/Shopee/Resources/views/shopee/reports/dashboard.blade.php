@extends('layouts.app')
@section('title', __('shopee::lang.report_dashboard'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-chart-line"></i> @lang('shopee::lang.report_dashboard')
    </h1>
</section>

<section class="content">
    @include('shopee::shopee.reports._nav')
    @include('shopee::shopee.reports._filter', ['action' => route('shopee.reports.dashboard')])

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #ee4d2d;">
                <span class="info-box-icon bg-red"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_revenue')</span>
                    <span class="info-box-number">{{ number_format($totalRevenue, 0, ',', '.') }} đ</span>
                    <span class="info-box-text" style="font-size: 11px;">
                        @if($revenueGrowth > 0)
                            <i class="fas fa-arrow-up text-green"></i> +{{ number_format($revenueGrowth, 1) }}%
                        @elseif($revenueGrowth < 0)
                            <i class="fas fa-arrow-down text-red"></i> {{ number_format($revenueGrowth, 1) }}%
                        @else
                            <i class="fas fa-minus"></i> 0%
                        @endif
                        @lang('shopee::lang.vs_previous_period')
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #00b894;">
                <span class="info-box-icon bg-green"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_orders')</span>
                    <span class="info-box-number">{{ number_format($totalOrders) }}</span>
                    <span class="info-box-text" style="font-size: 11px;">
                        @if($orderGrowth > 0)
                            <i class="fas fa-arrow-up text-green"></i> +{{ number_format($orderGrowth, 1) }}%
                        @elseif($orderGrowth < 0)
                            <i class="fas fa-arrow-down text-red"></i> {{ number_format($orderGrowth, 1) }}%
                        @else
                            <i class="fas fa-minus"></i> 0%
                        @endif
                        @lang('shopee::lang.vs_previous_period')
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #0984e3;">
                <span class="info-box-icon bg-blue"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.avg_order_value')</span>
                    <span class="info-box-number">{{ number_format($avgOrderValue, 0, ',', '.') }} đ</span>
                    <span class="info-box-text" style="font-size: 11px;">
                        @lang('shopee::lang.completed'): {{ $completedOrders }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #fdcb6e;">
                <span class="info-box-icon bg-yellow"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_shipping_fee')</span>
                    <span class="info-box-number">{{ number_format($totalShippingFee, 0, ',', '.') }} đ</span>
                    <span class="info-box-text" style="font-size: 11px;">
                        @lang('shopee::lang.cancelled'): {{ $cancelledOrders }} | @lang('shopee::lang.returned'): {{ $returnedOrders }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-area"></i> @lang('shopee::lang.revenue_by_day')</h3>
                </div>
                <div class="box-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Order Status Pie -->
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-pie"></i> @lang('shopee::lang.orders_by_status')</h3>
                </div>
                <div class="box-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Shop -->
    @if($revenueByShop->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-store"></i> @lang('shopee::lang.revenue_by_shop')</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.shop_name')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th class="text-right">@lang('shopee::lang.total_revenue')</th>
                                <th class="text-right">@lang('shopee::lang.avg_order_value')</th>
                                <th>@lang('shopee::lang.percentage')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($revenueByShop as $shopData)
                            <tr>
                                <td><i class="fas fa-store" style="color: #ee4d2d;"></i> {{ $shopData->shop_name }}</td>
                                <td class="text-right">{{ number_format($shopData->order_count) }}</td>
                                <td class="text-right"><strong>{{ number_format($shopData->revenue, 0, ',', '.') }} đ</strong></td>
                                <td class="text-right">{{ $shopData->order_count > 0 ? number_format($shopData->revenue / $shopData->order_count, 0, ',', '.') : 0 }} đ</td>
                                <td>
                                    @php $pct = $totalRevenue > 0 ? ($shopData->revenue / $totalRevenue) * 100 : 0; @endphp
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
    // Revenue by Day Chart
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueByDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
            datasets: [{
                label: '@lang("shopee::lang.revenue")',
                data: {!! json_encode($revenueByDay->pluck('revenue')->map(fn($v) => (float)$v)) !!},
                borderColor: '#ee4d2d',
                backgroundColor: 'rgba(238, 77, 45, 0.1)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y'
            }, {
                label: '@lang("shopee::lang.order_count")',
                data: {!! json_encode($revenueByDay->pluck('order_count')) !!},
                borderColor: '#0984e3',
                backgroundColor: 'rgba(9, 132, 227, 0.1)',
                fill: false,
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    type: 'linear', position: 'left',
                    ticks: { callback: v => (v/1000).toFixed(0) + 'k' }
                },
                y1: {
                    type: 'linear', position: 'right',
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Order Status Chart
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusData = @json($ordersByStatus);
    var statusLabels = {
        'new': '@lang("shopee::lang.status_new")',
        'confirmed': '@lang("shopee::lang.status_confirmed")',
        'packing': '@lang("shopee::lang.status_packing")',
        'ready_to_ship': '@lang("shopee::lang.status_ready_to_ship")',
        'shipped': '@lang("shopee::lang.status_shipped")',
        'delivering': '@lang("shopee::lang.status_delivering")',
        'completed': '@lang("shopee::lang.status_completed")',
        'returned': '@lang("shopee::lang.status_returned")',
        'cancelled': '@lang("shopee::lang.status_cancelled")'
    };
    var statusColors = {
        'new': '#ee4d2d', 'confirmed': '#0984e3', 'packing': '#6c5ce7',
        'ready_to_ship': '#fdcb6e', 'shipped': '#00b894', 'delivering': '#00cec9',
        'completed': '#2ecc71', 'returned': '#e17055', 'cancelled': '#636e72'
    };
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(k => statusLabels[k] || k),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: Object.keys(statusData).map(k => statusColors[k] || '#bdc3c7')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }
        }
    });
</script>
@endsection
