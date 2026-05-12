@extends('layouts.app')
@section('title', __('shopee::lang.shopee_settings'))

@section('content')

<!-- Content Header -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-store"></i> @lang('shopee::lang.shopee_integration')
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

    {{-- API Configuration --}}
    <div class="row">
        <div class="col-md-6">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('shopee::lang.api_configuration')])
                {!! Form::open(['route' => 'shopee.settings.save', 'method' => 'POST']) !!}

                <div class="form-group">
                    {!! Form::label('partner_id', __('shopee::lang.partner_id') . ':') !!}
                    {!! Form::text('partner_id', $config->partner_id ?? '', [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('shopee::lang.partner_id'),
                    ]) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('partner_key', __('shopee::lang.partner_key') . ':') !!}
                    {!! Form::text('partner_key', $config ? '********' : '', [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('shopee::lang.partner_key'),
                    ]) !!}
                    @if($config)
                        <p class="help-block text-muted">
                            <small>{{ __('shopee::lang.partner_key') }} is hidden for security. Enter a new value to update.</small>
                        </p>
                    @endif
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('sandbox_mode', 1, $config ? $config->sandbox_mode : true, ['class' => 'input-icheck']) !!}
                            @lang('shopee::lang.sandbox_mode')
                        </label>
                        <p class="help-block text-muted">
                            <small>@lang('shopee::lang.sandbox_mode_help')</small>
                        </p>
                    </div>
                </div>

                <button type="submit" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full">
                    <i class="fas fa-save"></i> @lang('shopee::lang.save_config')
                </button>

                {!! Form::close() !!}
            @endcomponent
        </div>

        {{-- Connected Shops --}}
        <div class="col-md-6">
            @component('components.widget', ['class' => 'box-success', 'title' => __('shopee::lang.connected_shops')])
                @slot('tool')
                    <div class="box-tools">
                        @if($config)
                            <a href="{{ route('shopee.connect') }}"
                               class="tw-dw-btn tw-bg-gradient-to-r tw-from-green-600 tw-to-green-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right">
                                <i class="fas fa-plus"></i> @lang('shopee::lang.connect_new_shop')
                            </a>
                        @endif
                    </div>
                @endslot

                @if($shops->count() > 0)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.shop_name')</th>
                                <th>@lang('shopee::lang.shop_id')</th>
                                <th>@lang('shopee::lang.status')</th>
                                <th>@lang('shopee::lang.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shops as $shop)
                                <tr>
                                    <td>
                                        <strong>{{ $shop->shop_name ?? 'Shop #' . $shop->shop_id }}</strong>
                                        @if($shop->region)
                                            <span class="label label-default">{{ $shop->region }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $shop->shop_id }}</td>
                                    <td>
                                        @if($shop->isConnected())
                                            <span class="label label-success">
                                                <i class="fas fa-check-circle"></i> @lang('shopee::lang.connected')
                                            </span>
                                        @elseif($shop->status === 'expired')
                                            <span class="label label-warning">
                                                <i class="fas fa-exclamation-triangle"></i> @lang('shopee::lang.expired')
                                            </span>
                                        @else
                                            <span class="label label-danger">
                                                <i class="fas fa-times-circle"></i> @lang('shopee::lang.disconnected')
                                            </span>
                                        @endif
                                        @if($shop->token_expires_at)
                                            <br><small class="text-muted">
                                                @lang('shopee::lang.token_expires_at'): {{ $shop->token_expires_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($shop->status === 'connected' || $shop->status === 'expired')
                                            {!! Form::open(['route' => ['shopee.refresh-token', $shop->id], 'method' => 'POST', 'style' => 'display:inline']) !!}
                                                <button type="submit" class="btn btn-xs btn-info" title="@lang('shopee::lang.refresh_token')">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                            {!! Form::close() !!}
                                        @endif

                                        {!! Form::open(['route' => ['shopee.disconnect', $shop->id], 'method' => 'POST', 'style' => 'display:inline']) !!}
                                            <button type="submit" class="btn btn-xs btn-danger"
                                                    title="@lang('shopee::lang.disconnect')"
                                                    onclick="return confirm('@lang('shopee::lang.confirm_disconnect')')">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-muted" style="padding: 30px;">
                        <i class="fas fa-store" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="tw-mt-3">@lang('shopee::lang.no_shops_connected')</p>
                    </div>
                @endif
            @endcomponent
        </div>
    </div>

    {{-- Recent Activity Logs --}}
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-warning', 'title' => __('shopee::lang.recent_activity')])
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ route('shopee.sync-logs') }}" class="btn btn-sm btn-default pull-right">
                            <i class="fas fa-list"></i> @lang('shopee::lang.view_all_logs')
                        </a>
                    </div>
                @endslot

                @if($recentLogs->count() > 0)
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>@lang('shopee::lang.date')</th>
                                <th>@lang('shopee::lang.type')</th>
                                <th>@lang('shopee::lang.status')</th>
                                <th>@lang('shopee::lang.message')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                                <tr>
                                    <td><small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small></td>
                                    <td>
                                        <span class="label label-default">
                                            @lang('shopee::lang.' . $log->type)
                                        </span>
                                    </td>
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
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted text-center">@lang('shopee::lang.no_logs')</p>
                @endif
            @endcomponent
        </div>
    </div>

</section>
@endsection
