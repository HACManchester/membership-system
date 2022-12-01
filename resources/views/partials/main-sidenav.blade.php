<style>

    .topNav {
        position: absolute;
        left: 0;
        right: 0;
        z-index: 100;
    }

    .topNavItem{
        flex: 0 1 auto;
    }

    .topNavItem.right {
        margin-left: auto;
    }

    li.topNavItem {
        list-style: none;
        padding: 1em;
    }

    mainNav li {
        border-left: 1px solid #aaa;
    }

    .logo {
        padding: 1em;
    }

    .mainSidenav {
        box-shadow: 4px 4px 10px -7px;
        top: 50px;
        padding-top: 1em;
        position: absolute;
        overflow-y: scroll;
    }

    @media (max-width: 766px) {
        .mainSidenav {
            z-index: 10000;
        }
    }

    .login-container {
        border-radius: 1em;
    }

    .panel, .well, .alert {
        border-radius: 1em;
    }

    .panel-heading {
        border-radius: 1em 1em 0 0;
    }

    .list-group-item:first-child {
        border-radius: 1em 1em 0 0;
    }

    .list-group-item:last-child {
        border-radius: 0 0 1em 1em;
    }

    .btn {
        border-radius: 5px;
    }

    #pageTitle .titles, #pageTitle {
        border-bottom: 1px solid black;
    }

    #pageTitle .titles img {
        margin-top: -20px;
    }

    #pageTitle {
        background: linear-gradient(90deg, rgba(255,240,0,1) 0%, rgba(255,250,170,1) 100%);
    }

    .form-control {
        border: 1px solid;
        border-left: 3px solid;
        border-radius: 5px;
        margin-right: 1em;
    }

    .input-group-addon {
        border-radius: 5px;
        border: 1px solid;
    }

    .register-container, .login-container {
        margin-top: 50px;
        border-radius: 1em;
        border-left: 3px solid lightgrey;
    }
</style>
<!-- TOP NAV TO OTHER SITES-->
<nav role="navigation" class="topNav" style="border-bottom: 1px solid black; background: white; height: 50px">
<div style="display: flex; overflow:hidden;">
    <li class="topNavItem">
        <a href="https://hacman.org.uk" target="_blank">Website</a>
    </li>
    <li class="topNavItem">
        <a href="https://list.hacman.org.uk" target="_blank">Forum</a>
    </li>
    <li class="topNavItem  hidden-xs visible-s">
        <a href="https://moodle.hacman.org.uk" target="_blank">Moodle</a>
    </li>
    <li class="topNavItem">
        <a href="https://www.hacman.org.uk/covid-19-information/" target="_blank">COVID-19</a>
    </li>
    <li class="topNavItem  hidden-xs visible-s">
        <a href="https://docs.hacman.org.uk" target="_blank">Documentation</a>
    </li>

    @if (Auth::guest())
        <li class="topNavItem right"><a href="/login">🔑 Login</a></li>
        <li class="topNavItem"><a href="/register">✔️ Become a Member</a></li>
    @else
        <li class="topNavItem right">
            <span class="hidden-xs">
                (<a href="/account/{!! Auth::user()->id !!}">{!! Auth::user()->name !!}</a>)
            </span>
            <a href="/logout">🔑 Logout</a>
        </li>
    @endif
    </div>
</nav>

<!-- NAV TO MEMBER PAGES -->
<nav class="mainSidenav" role="navigation">
    <div style="border-bottom: 3px dotted #ddd;">
        <span class="sidenav-brand">
            <a href="{{ route('home') }}">
                <img class="" src="/img/logo-new.png" height="100" style="display:block; margin: 0 auto;"/>
            </a>
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
                    🙂 Your Membership
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

            {!! HTML::sideNavLink('💳 Manage Your Balance', 'account.balance.index', [Auth::id()]) !!}
            {!! HTML::sideNavLink('ℹ️ General Induction', 'account.induction.show', [Auth::id()], !Auth::user()->induction_completed) !!}
           
        </ul>
        @endif

        @if (Auth::guest())
            <div style="margin: 1em">
                <p>Hackspace Manchester is a place for people who make things to make things.</p>
                <h3>Do we know eachother?</h3>
                <li class="topNavItem right"><a href="/login">🔑 Login</a></li>
                <li class="topNavItem"><a href="/register">✔️ Become a Member</a></li>
            </div>
        @endif
    </div>

    @if (!Auth::guest())
        <ul class="nav">
            
            {!! HTML::sideNavLink('Members', 'members.index') !!}
            {!! HTML::sideNavLink('Member Storage', 'storage_boxes.index') !!}
            {!! HTML::sideNavLink('Large Project Storage', 'projects_storage.index') !!}
            {!! HTML::sideNavLink('Tools and Equipment', 'equipment.index') !!}
            {!! HTML::sideNavLink('Stats', 'stats.index') !!}
            {!! HTML::sideNavLink('Teams', 'groups.index') !!}
            
            @if(!Auth::guest())
                @if (Auth::user()->isAdmin())
                    {!! HTML::sideNavLink('👮 Admin', 'admin') !!}
                    {!! HTML::sideNavLink('👮 Manage Members', 'account.index') !!}
                    {!! HTML::sideNavLink('👮 Log Files', 'logs') !!}
                    {!! HTML::sideNavLink('💌 Newsletter', 'newsletter') !!}
                @endif

                @if (Auth::user()->hasRole('comms') || Auth::user()->isAdmin())
                    {!! HTML::sideNavLink('Members Inductions', 'account.induction.index') !!}
                @endif

                @if (Auth::user()->hasRole('acs') || Auth::user()->isAdmin())
                    {!! HTML::sideNavLink('🔑 Devices', 'devices.index') !!}
                @endif

                @if (Auth::user()->hasRole('finance') || Auth::user()->isAdmin())
                    {!! HTML::sideNavLink('💰 Payments', 'payments.index') !!}
                    {!! HTML::sideNavLink('💰 Expenses <span class="badge js-expenses-count"></span>', 'expenses.index') !!}
                @endif
            @endif
        </ul>
    @endif
    
</nav>

