@extends('layouts.app')
@section('title', __('shopee::lang.report_reconciliation'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-balance-scale"></i> @lang('shopee::lang.report_reconciliation')
    </h1>
</section>

<section class="content">
    @include('shopee::shopee.reports._nav')
    @include('shopee::shopee.reports._filter', ['action' => route('shopee.reports.reconciliation')])

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #ee4d2d;">
                <span class="info-box-icon bg-red"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_order_amount')</span>
                    <span class="info-box-number">{{ number_format($totalAmount, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #2ecc71;">
                <span class="info-box-icon bg-green"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.completed_amount')</span>
                    <span class="info-box-number">{{ number_format($completedAmount, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #fdcb6e;">
                <span class="info-box-icon bg-yellow"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.pending_amount')</span>
                    <span class="info-box-number">{{ number_format($pendingAmount, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box" style="border-left: 4px solid #0984e3;">
                <span class="info-box-icon bg-blue"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('shopee::lang.total_shipping_fee')</span>
                    <span class="info-box-number">{{ number_format($totalShipping, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-bar"></i> @lang('shopee::lang.daily_reconciliation')</h3>
                </div>
                <div class="box-body">
                    <canvas id="reconChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Summary by Status -->
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-list-alt"></i> @lang('shopee::lang.summary_by_status')</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.status')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th class="text-right">@lang('shopee::lang.total_amount')</th>
                                <th class="text-right">@lang('shopee::lang.shipping_fee')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusNames = [
                                    'new' => __('shopee::lang.status_new'),
                                    'confirmed' => __('shopee::lang.status_confirmed'),
                                    'packing' => __('shopee::lang.status_packing'),
                                    'ready_to_ship' => __('shopee::lang.status_ready_to_ship'),
                                    'shipped' => __('shopee::lang.status_shipped'),
                                    'delivering' => __('shopee::lang.status_delivering'),
                                    'completed' => __('shopee::lang.status_completed'),
                                    'returned' => __('shopee::lang.status_returned'),
                                    'cancelled' => __('shopee::lang.status_cancelled'),
                                ];
                                $statusColors = [
                                    'new' => '#ee4d2d', 'confirmed' => '#0984e3', 'packing' => '#6c5ce7',
                                    'ready_to_ship' => '#fdcb6e', 'shipped' => '#00b894', 'delivering' => '#00cec9',
                                    'completed' => '#2ecc71', 'returned' => '#e17055', 'cancelled' => '#636e72',
                                ];
                            @endphp
                            @foreach($summary as $status => $data)
                            <tr>
                                <td>
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $statusColors[$status] ?? '#bdc3c7' }};margin-right:5px;"></span>
                                    {{ $statusNames[$status] ?? $status }}
                                </td>
                                <td class="text-right">{{ number_format($data->count) }}</td>
                                <td class="text-right">{{ number_format($data->total_amount, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($data->total_shipping, 0, ',', '.') }} đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: #f5f5f5;">
                                <td>@lang('shopee::lang.total')</td>
                                <td class="text-right">{{ number_format($summary->sum('count')) }}</td>
                                <td class="text-right">{{ number_format($summary->sum('total_amount'), 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($summary->sum('total_shipping'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- By Shop -->
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-store"></i> @lang('shopee::lang.reconciliation_by_shop')</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.shop_name')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th class="text-right">@lang('shopee::lang.revenue')</th>
                                <th class="text-right">@lang('shopee::lang.completed_amount')</th>
                                <th class="text-right">@lang('shopee::lang.shipping_fee')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reconByShop as $shop)
                            <tr>
                                <td><i class="fas fa-store" style="color: #ee4d2d;"></i> {{ $shop->shop_name }}</td>
                                <td class="text-right">{{ number_format($shop->orders) }}</td>
                                <td class="text-right">{{ number_format($shop->revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right text-green">{{ number_format($shop->completed_revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($shop->shipping, 0, ',', '.') }} đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Detail Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-calendar-alt"></i> @lang('shopee::lang.daily_detail')</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.date')</th>
                                <th class="text-right">@lang('shopee::lang.order_count')</th>
                                <th class="text-right">@lang('shopee::lang.revenue')</th>
                                <th class="text-right">@lang('shopee::lang.completed_amount')</th>
                                <th class="text-right">@lang('shopee::lang.shipping_fee')</th>
                                <th class="text-right">@lang('shopee::lang.buyer_paid')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyRecon as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</td>
                                <td class="text-right">{{ number_format($day->orders) }}</td>
                                <td class="text-right">{{ number_format($day->revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right text-green">{{ number_format($day->completed_revenue, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($day->shipping, 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($day->buyer_paid, 0, ',', '.') }} đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background: #f5f5f5;">
                                <td>@lang('shopee::lang.total')</td>
                                <td class="text-right">{{ number_format($dailyRecon->sum('orders')) }}</td>
                                <td class="text-right">{{ number_format($dailyRecon->sum('revenue'), 0, ',', '.') }} đ</td>
                                <td class="text-right text-green">{{ number_format($dailyRecon->sum('completed_revenue'), 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($dailyRecon->sum('shipping'), 0, ',', '.') }} đ</td>
                                <td class="text-right">{{ number_format($dailyRecon->sum('buyer_paid'), 0, ',', '.') }} đ</td>
                            </tr>
                        </tfoot>
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
    new Chart(document.getElementById('reconChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyRecon->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
            datasets: [
                {
                    label: '@lang("shopee::lang.revenue")',
                    data: {!! json_encode($dailyRecon->pluck('revenue')->map(fn($v) => (float)$v)) !!},
                    backgroundColor: 'rgba(238, 77, 45, 0.6)',
                    stack: 'revenue'
                },
                {
                    label: '@lang("shopee::lang.completed_amount")',
                    data: {!! json_encode($dailyRecon->pluck('completed_revenue')->map(fn($v) => (float)$v)) !!},
                    backgroundColor: 'rgba(46, 204, 113, 0.6)',
                    stack: 'completed'
                },
                {
                    label: '@lang("shopee::lang.shipping_fee")',
                    data: {!! json_encode($dailyRecon->pluck('shipping')->map(fn($v) => (float)$v)) !!},
                    backgroundColor: 'rgba(9, 132, 227, 0.4)',
                    stack: 'shipping'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { ticks: { callback: v => (v/1000).toFixed(0) + 'k' } }
            },
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection
