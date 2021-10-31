<nav class="mainSidenav" role="navigation">

    <header>
        <span class="sidenav-brand">
            <a href="{{ route('home') }}"><img class="" src="/img/logo-new.png" height="100" /></a>
            @if (!Auth::guest())
                @if (Auth::user()->online_only)
                    <span class="label label-warning">Online Only</span>
                @else
                    @if (Auth::user()->status != 'active')
                        {!! HTML::statusLabel(Auth::user()->status) !!}
                    @endif
                @endif
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
            {!! HTML::sideNavLink('Getting Started', 'account.induction.show', [Auth::id()]) !!}
            <li class="withAction">
                <a href="https://list.hacman.org.uk">Forum</a>
                <a href="https://list.hacman.org.uk" style="position:absolute;right: 0;top:0">
                    <i class="material-icons md-18">link</i>
                </a>
            </li>
            <li class="withAction">
                <a href="https://moodle.hacman.org.uk">Moodle</a>
                <a href="https://moodle.hacman.org.uk" style="position:absolute;right: 0;top:0">
                    <i class="material-icons md-18">link</i>
                </a>
            </li>
            <li class="withAction">
                <a href="https://www.hacman.org.uk/covid-19-information/">COVID-19 information</a>
                <a href="https://www.hacman.org.uk/covid-19-information/" style="position:absolute;right: 0;top:0">
                    <i class="material-icons md-18">link</i>
                </a>
            </li>
	        <li class="withAction">
                <a href="https://docs.hacman.org.uk">Documentation</a>
                <a href="https://docs.hacman.org.uk" style="position:absolute;right: 0;top:0">
                    <i class="material-icons md-18">link</i>
                </a>
            </li>
        </ul>
        @endif
    </header>


    @if (!Auth::guest())
        <ul class="nav">
            
            {!! HTML::sideNavLink('👥 Members', 'members.index') !!}
            {!! HTML::sideNavLink('📦 Member Storage', 'storage_boxes.index') !!}
            {!! HTML::sideNavLink('🐘 Large Project Storage', 'projects_storage.index') !!}
            {!! HTML::sideNavLink('🧰 Tools and Equipment', 'equipment.index') !!}
            {!! HTML::sideNavLink('🧮 Stats', 'stats.index') !!}
            {!! HTML::sideNavLink('🤝 Teams', 'groups.index') !!}
            
            @if(!Auth::guest())
                @if (Auth::user()->hasRole('admin'))
                    {!! HTML::sideNavLink('👮 Manage Members', 'account.index') !!}
                    {!! HTML::sideNavLink('👮 Activity', 'activity.index') !!}
                    {!! HTML::sideNavLink('👮 Proposals', 'proposals.index') !!} 
                    {!! HTML::sideNavLink('👮 Log Files', 'logs') !!}
                @endif

                @if (Auth::user()->hasRole('comms'))
                    {!! HTML::sideNavLink('Members Inductions', 'account.induction.index') !!}
                @endif

                @if (Auth::user()->hasRole('acs'))
                    {!! HTML::sideNavLink('🔑 Devices', 'devices.index') !!}
                @endif

                @if (Auth::user()->hasRole('finance'))
                    {!! HTML::sideNavLink('💰 Payments', 'payments.index') !!}
                    {!! HTML::sideNavLink('💰 Expenses <span class="badge js-expenses-count"></span>', 'expenses.index') !!}
                @endif
            @endif
        </ul>
    @endif

    <ul class="nav secondaryNav">
        @if (Auth::guest())
            {!! HTML::sideNavLink('🔑 Login', 'login') !!}
            {!! HTML::sideNavLink('✔️ Become a Member', 'register') !!}
        @else
            {!! HTML::sideNavLink('🔑 Logout', 'logout') !!}
        @endif
    </ul>
</nav>

