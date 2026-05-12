@extends('layouts.app')
@section('title', __('shopee::lang.report_order_analysis'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-chart-bar"></i> @lang('shopee::lang.report_order_analysis')
    </h1>
</section>

<section class="content">
    @include('shopee::shopee.reports._nav')
    @include('shopee::shopee.reports._filter', ['action' => route('shopee.reports.order-analysis')])

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #2ecc71;">
                <span class="info-box-icon bg-green"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.completion_rate')</span>
                    <span class="info-box-number">{{ number_format($completionRate, 1) }}%</span>
                    <span class="info-box-text" style="font-size: 11px;">{{ $ordersByStatus->get('completed')->count ?? 0 }} / {{ $total }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #e74c3c;">
                <span class="info-box-icon bg-red"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.cancel_rate')</span>
                    <span class="info-box-number">{{ number_format($cancelRate, 1) }}%</span>
                    <span class="info-box-text" style="font-size: 11px;">{{ $ordersByStatus->get('cancelled')->count ?? 0 }} @lang('shopee::lang.orders')</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #e17055;">
                <span class="info-box-icon bg-yellow"><i class="fas fa-undo"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.return_rate')</span>
                    <span class="info-box-number">{{ number_format($returnRate, 1) }}%</span>
                    <span class="info-box-text" style="font-size: 11px;">{{ $ordersByStatus->get('returned')->count ?? 0 }} @lang('shopee::lang.orders')</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #0984e3;">
                <span class="info-box-icon bg-blue"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.avg_processing_time')</span>
                    <span class="info-box-number">
                        @if($avgProcessingTime)
                            @if($avgProcessingTime > 60)
                                {{ number_format($avgProcessingTime / 60, 1) }}h
                            @else
                                {{ number_format($avgProcessingTime, 0) }} @lang('shopee::lang.minutes')
                            @endif
                        @else
                            N/A
                        @endif
                    </span>
                    <span class="info-box-text" style="font-size: 11px;">@lang('shopee::lang.from_created_to_confirmed')</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Trend Chart -->
        <div class="col-md-8">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-area"></i> @lang('shopee::lang.order_trend')</h3>
                </div>
                <div class="box-body">
                    <canvas id="orderTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-list"></i> @lang('shopee::lang.orders_by_status')</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table">
                        @php
                            $statusLabels = [
                                'new' => ['shopee::lang.status_new', '#ee4d2d'],
                                'confirmed' => ['shopee::lang.status_confirmed', '#0984e3'],
                                'packing' => ['shopee::lang.status_packing', '#6c5ce7'],
                                'ready_to_ship' => ['shopee::lang.status_ready_to_ship', '#fdcb6e'],
                                'shipped' => ['shopee::lang.status_shipped', '#00b894'],
                                'delivering' => ['shopee::lang.status_delivering', '#00cec9'],
                                'completed' => ['shopee::lang.status_completed', '#2ecc71'],
                                'returned' => ['shopee::lang.status_returned', '#e17055'],
                                'cancelled' => ['shopee::lang.status_cancelled', '#636e72'],
                            ];
                        @endphp
                        @foreach($statusLabels as $status => [$label, $color])
                            @if($ordersByStatus->has($status))
                            <tr>
                                <td>
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $color }};margin-right:5px;"></span>
                                    @lang($label)
                                </td>
                                <td class="text-right"><strong>{{ number_format($ordersByStatus[$status]->count) }}</strong></td>
                                <td class="text-right">{{ number_format($ordersByStatus[$status]->total, 0, ',', '.') }} đ</td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Orders by Hour -->
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-clock"></i> @lang('shopee::lang.orders_by_hour')</h3>
                </div>
                <div class="box-body">
                    <canvas id="hourChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-credit-card"></i> @lang('shopee::lang.payment_methods')</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.payment_method')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th>@lang('shopee::lang.percentage')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentMethods as $pm)
                            <tr>
                                <td>{{ $pm->payment_method ?: 'N/A' }}</td>
                                <td class="text-right">{{ number_format($pm->count) }}</td>
                                <td>
                                    @php $pct = $total > 0 ? ($pm->count / $total) * 100 : 0; @endphp
                                    <div class="progress progress-sm" style="margin-bottom: 0;">
                                        <div class="progress-bar bg-blue" style="width: {{ $pct }}%"></div>
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
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Order Trend
    new Chart(document.getElementById('orderTrendChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($ordersByDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
            datasets: [
                { label: '@lang("shopee::lang.total_orders")', data: {!! json_encode($ordersByDay->pluck('total')) !!}, backgroundColor: 'rgba(9,132,227,0.6)', stack: 'total' },
                { label: '@lang("shopee::lang.status_completed")', data: {!! json_encode($ordersByDay->pluck('completed')) !!}, backgroundColor: 'rgba(46,204,113,0.6)', stack: 'detail' },
                { label: '@lang("shopee::lang.status_cancelled")', data: {!! json_encode($ordersByDay->pluck('cancelled')) !!}, backgroundColor: 'rgba(231,76,60,0.6)', stack: 'detail' },
                { label: '@lang("shopee::lang.status_returned")', data: {!! json_encode($ordersByDay->pluck('returned')) !!}, backgroundColor: 'rgba(225,112,85,0.6)', stack: 'detail' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true } },
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Orders by Hour
    var hours = Array.from({length: 24}, (_, i) => i);
    var hourData = @json($ordersByHour);
    new Chart(document.getElementById('hourChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: hours.map(h => h + ':00'),
            datasets: [{
                label: '@lang("shopee::lang.order_count")',
                data: hours.map(h => hourData[h] || 0),
                backgroundColor: hours.map(h => (h >= 18 && h <= 23) ? 'rgba(238,77,45,0.7)' : 'rgba(9,132,227,0.5)')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection
