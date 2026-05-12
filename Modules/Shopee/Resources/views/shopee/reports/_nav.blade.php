<div style="margin-bottom: 15px;">
    <ul class="nav nav-pills">
        <li class="{{ request()->routeIs('shopee.reports.dashboard') ? 'active' : '' }}">
            <a href="{{ route('shopee.reports.dashboard', request()->only('shop_id', 'date_from', 'date_to')) }}">
                <i class="fas fa-chart-line"></i> @lang('shopee::lang.report_dashboard')
            </a>
        </li>
        <li class="{{ request()->routeIs('shopee.reports.order-analysis') ? 'active' : '' }}">
            <a href="{{ route('shopee.reports.order-analysis', request()->only('shop_id', 'date_from', 'date_to')) }}">
                <i class="fas fa-chart-bar"></i> @lang('shopee::lang.report_order_analysis')
            </a>
        </li>
        <li class="{{ request()->routeIs('shopee.reports.top-products') ? 'active' : '' }}">
            <a href="{{ route('shopee.reports.top-products', request()->only('shop_id', 'date_from', 'date_to')) }}">
                <i class="fas fa-trophy"></i> @lang('shopee::lang.report_top_products')
            </a>
        </li>
        <li class="{{ request()->routeIs('shopee.reports.shipping') ? 'active' : '' }}">
            <a href="{{ route('shopee.reports.shipping', request()->only('shop_id', 'date_from', 'date_to')) }}">
                <i class="fas fa-truck"></i> @lang('shopee::lang.report_shipping')
            </a>
        </li>
        <li class="{{ request()->routeIs('shopee.reports.reconciliation') ? 'active' : '' }}">
            <a href="{{ route('shopee.reports.reconciliation', request()->only('shop_id', 'date_from', 'date_to')) }}">
                <i class="fas fa-balance-scale"></i> @lang('shopee::lang.report_reconciliation')
            </a>
        </li>
    </ul>
</div>
