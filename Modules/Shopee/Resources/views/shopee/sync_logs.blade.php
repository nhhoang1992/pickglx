@extends('layouts.app')
@section('title', __('shopee::lang.sync_logs'))

@section('content')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-history"></i> @lang('shopee::lang.sync_logs')
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                @slot('tool')
                    <div class="box-tools">
                        <form method="GET" action="{{ route('shopee.sync-logs') }}" class="form-inline pull-right">
                            <select name="type" class="form-control input-sm" onchange="this.form.submit()">
                                <option value="">@lang('shopee::lang.all') @lang('shopee::lang.type')</option>
                                @foreach(['auth', 'product_sync', 'stock_sync', 'order_sync', 'order_action', 'other'] as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                        @lang('shopee::lang.' . $type)
                                    </option>
                                @endforeach
                            </select>
                            <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                <option value="">@lang('shopee::lang.all') @lang('shopee::lang.status')</option>
                                @foreach(['success', 'error', 'pending'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        @lang('shopee::lang.' . $status)
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endslot

                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>@lang('shopee::lang.date')</th>
                            <th>@lang('shopee::lang.shop_name')</th>
                            <th>@lang('shopee::lang.type')</th>
                            <th>@lang('shopee::lang.status')</th>
                            <th>@lang('shopee::lang.message')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td><small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small></td>
                                <td>{{ $log->shop->shop_name ?? 'N/A' }}</td>
                                <td><span class="label label-default">@lang('shopee::lang.' . $log->type)</span></td>
                                <td>
                                    @if($log->status === 'success')
                                        <span class="label label-success">@lang('shopee::lang.success')</span>
                                    @elseif($log->status === 'error')
                                        <span class="label label-danger">@lang('shopee::lang.error')</span>
                                    @else
                                        <span class="label label-warning">@lang('shopee::lang.pending')</span>
                                    @endif
                                </td>
                                <td>{{ $log->message }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">@lang('shopee::lang.no_logs')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="text-center">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection
