<nav id="mainTabBar">
    <ul role="tablist">
        <li class="{{ request()->routeIs('payments.index') ? 'active' : '' }}">
            <a href="{{ route('payments.index') }}">All Payments</a>
        </li>
        <li class="{{ request()->routeIs('payments.overview') ? 'active' : '' }}">
            <a href="{{ route('payments.overview') }}">Overview</a>
        </li>
        <li class="{{ request()->routeIs('payments.sub-charges') ? 'active' : '' }}">
            <a href="{{ route('payments.sub-charges') }}">Subscription Charges</a>
        </li>
        <li class="{{ request()->routeIs('payments.possible-duplicates') ? 'active' : '' }}">
            <a href="{{ route('payments.possible-duplicates') }}">Possible Duplicates</a>
        </li>
        <li class="{{ request()->routeIs('payments.balances') ? 'active' : '' }}">
            <a href="{{ route('payments.balances') }}">Balances</a>
        </li>
    </ul>
</nav>