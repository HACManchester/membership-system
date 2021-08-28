<nav class="mainSidenav" role="navigation">

    <header>
        <span class="sidenav-brand">
            <a href="{{ route('home') }}"><img class="" src="/img/logo-new.png" height="100" /></a>
            @if (!Auth::guest() && (Auth::user()->status != 'active') )
                {!! HTML::statusLabel(Auth::user()->status) !!}
            @endif
        </span>
        @if (!Auth::guest())
        <ul class="nav memberAccountLinks">
            <li class="withAction">
                <a href="{{ route('account.show', [Auth::id()]) }}">
                    Your Membership
                    @if (count(Auth::user()->getAlerts()) > 0)
                    <span class="badge">{{ count(Auth::user()->getAlerts()) }}</span>
                    @endif
                </a>
                <a class="toggleSettings" href="">
                    <i class="material-icons md-18">settings</i>
                </a>
            </li>
            <ul class="nav nested-nav accountSettings">
                {!! HTML::sideNavLink('Edit Your Account', 'account.edit', [Auth::id()]) !!}
                {!! HTML::sideNavLink('Edit Your Profile', 'account.profile.edit', [Auth::id()]) !!}
            </ul>

            {!! HTML::sideNavLink('Manage Your Balance', 'account.balance.index', [Auth::id()]) !!}
            {!! HTML::sideNavLink('Citizen Hacman Commitment', 'account.induction.show', [Auth::id()]) !!}
            {!! HTML::sideNavLink('Notifications <span class="badge js-notifications-count"></span>', 'notifications.index') !!}
            
            <li><a href="https://help.hacman.org.uk">Helpdesk</a></li>


        </ul>
        @endif
    </header>


    <ul class="nav">
        @if (!Auth::guest())
        {!! HTML::sideNavLink('Members', 'members.index') !!}
        {!! HTML::sideNavLink('Member Storage', 'storage_boxes.index') !!}
        {!! HTML::sideNavLink('Tools and Equipment', 'equipment.index') !!}
        {!! HTML::sideNavLink('Stats', 'stats.index') !!}
        {!! HTML::sideNavLink('Resources', 'resources.index') !!}
        {!! HTML::sideNavLink('Teams', 'groups.index') !!}
        @endif
        @if (!Auth::guest() && Auth::user()->hasRole('admin'))
        {!! HTML::sideNavLink('Activity', 'activity.index') !!}
        {!! HTML::sideNavLink('Proposals', 'proposals.index') !!} 
        @endif
        @if (!Auth::guest() && Auth::user()->hasRole('comms'))
            {!! HTML::sideNavLink('Members Inductions', 'account.induction.index') !!}
        @endif
        @if (!Auth::guest() && Auth::user()->hasRole('admin'))
            {!! HTML::sideNavLink('Manage Members', 'account.index') !!}
        @endif
        @if (!Auth::guest() && Auth::user()->hasRole('acs'))
            {!! HTML::sideNavLink('Devices', 'devices.index') !!}
        @endif
        @if (!Auth::guest() && Auth::user()->hasRole('finance'))
            {!! HTML::sideNavLink('Payments', 'payments.index') !!}
            {!! HTML::sideNavLink('Expenses <span class="badge js-expenses-count"></span>', 'expenses.index') !!}
        @endif
        @if (!Auth::guest() && Auth::user()->hasRole('admin'))
            {!! HTML::sideNavLink('Log Files', 'logs') !!}
        @endif
        <li><a href="https://wiki.hacman.org.uk">Wiki</a></li>
        <li><a href="https://list.hacman.org.uk">Forum</a></li>

    </ul>

    <ul class="nav secondaryNav">
        @if (Auth::guest())
            {!! HTML::sideNavLink('Login', 'login') !!}
            {!! HTML::sideNavLink('Become a Member', 'register') !!}
        @else

            {!! HTML::sideNavLink('Logout', 'logout') !!}
        @endif
    </ul>
</nav>
