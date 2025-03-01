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
                <img src="/img/logo-new.png" height="100" style="display:block; margin: 0 auto;"/>
            </a>
            @if (!Auth::guest())
                @if (Auth::user()->online_only)
                    <span class="label label-warning">Online Only</span>
                @else
                    @if (Auth::user()->status != 'active')
                        @include('partials.components.status-label', ['status' => Auth::user()->status])
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
                @include('partials.components.side-nav-link', ['name' => 'Edit Your Account', 'route' => 'account.edit', 'routeParams' => [Auth::id()]])
                @include('partials.components.side-nav-link', ['name' => 'Edit Your Profile', 'route' => 'account.profile.edit', 'routeParams' => [Auth::id()]])
            </ul>

            @include('partials.components.side-nav-link', ['name' => '💳 Manage Your Balance', 'route' => 'account.balance.index', 'routeParams' => [Auth::id()]])
            @include('partials.components.side-nav-link', ['name' => 'ℹ️ General Induction', 'route' => 'general-induction.show', 'routeParams' => [Auth::id()], 'highlight' => !Auth::user()->induction_completed])

            <li><a href="https://list.hacman.org.uk" target="_blank">💬 Forum</a></li>
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
            
            @include('partials.components.side-nav-link', ['name' => 'Members', 'route' => 'members.index'])
            @include('partials.components.side-nav-link', ['name' => 'Member Storage', 'route' => 'storage_boxes.index'])
            @include('partials.components.side-nav-link', ['name' => 'Tools and Equipment', 'route' => 'equipment.index'])
            @include('partials.components.side-nav-link', ['name' => 'Stats', 'route' => 'stats.index'])
            @include('partials.components.side-nav-link', ['name' => 'Area Coordinators', 'route' => 'equipment_area.index'])
            @include('partials.components.side-nav-link', ['name' => 'Maintainer Groups', 'route' => 'maintainer_groups.index'])
            
            @if(!Auth::guest())
                @if (Auth::user()->isAdmin())
                    @include('partials.components.side-nav-link', ['name' => '👮 Admin', 'route' => 'admin'])
                    @include('partials.components.side-nav-link', ['name' => '👮 Manage Members', 'route' => 'account.index'])
                    @include('partials.components.side-nav-link', ['name' => '👮 Log Files', 'route' => 'logs'])
                    @include('partials.components.side-nav-link', ['name' => '💌 Newsletter', 'route' => 'newsletter'])
                @endif

                @if (Auth::user()->hasRole('finance') || Auth::user()->isAdmin())
                    @include('partials.components.side-nav-link', ['name' => '💰 Payments', 'route' => 'payments.index'])
                @endif
            @endif
        </ul>
    @endif
    
</nav>

