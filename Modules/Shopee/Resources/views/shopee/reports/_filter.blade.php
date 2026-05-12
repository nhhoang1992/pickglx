<div class="box box-solid" style="margin-bottom: 15px;">
    <div class="box-body" style="padding: 10px 15px;">
        <form method="GET" action="{{ $action }}" class="form-inline">
            <div class="form-group" style="margin-right: 10px;">
                <select name="shop_id" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="">@lang('shopee::lang.all_shops')</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ $shopId == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-right: 5px;">
                <input type="date" name="date_from" class="form-control input-sm"
                       value="{{ $dateFrom->format('Y-m-d') }}">
            </div>
            <div class="form-group" style="margin-right: 5px;">
                <span>→</span>
            </div>
            <div class="form-group" style="margin-right: 10px;">
                <input type="date" name="date_to" class="form-control input-sm"
                       value="{{ $dateTo->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> @lang('shopee::lang.filter')
            </button>
            @if(isset($extraFilters))
                {!! $extraFilters !!}
            @endif
        </form>
    </div>
</div>
