<nav id="mainTabBar">
    <ul role="tablist">
        <li class="{{ request()->routeIs('payments.index') ? 'active' : '' }}">
            {!! link_to_route('payments.index', 'All Payments') !!}
        </li>
        <li class="{{ request()->routeIs('payments.overview') ? 'active' : '' }}">
            {!! link_to_route('payments.overview', 'Overview') !!}
        </li>
        <li class="{{ request()->routeIs('payments.sub-charges') ? 'active' : '' }}">
            {!! link_to_route('payments.sub-charges', 'Subscription Charges') !!}
        </li>
        <li class="{{ request()->routeIs('payments.possible-duplicates') ? 'active' : '' }}">
            {!! link_to_route('payments.possible-duplicates', 'Possible Duplicates') !!}
        </li>
        <li class="{{ request()->routeIs('payments.balances') ? 'active' : '' }}">
            {!! link_to_route('payments.balances', 'Balances') !!}
        </li>
    </ul>
</nav>