<style>
    .login-container {
        border-radius: 1em;
    }

    .panel,
    .well,
    .alert {
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

    #pageTitle .titles,
    #pageTitle {
        border-bottom: 1px solid black;
    }

    #pageTitle .titles img {
        margin-top: -20px;
    }

    #pageTitle {
        background: linear-gradient(90deg, rgba(255, 240, 0, 1) 0%, rgba(255, 250, 170, 1) 100%);
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

    .register-container,
    .login-container {
        margin-top: 50px;
        border-radius: 1em;
        border-left: 3px solid lightgrey;
    }
</style>
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

<nav class="mainSidenav" role="navigation">
    <div class="sidenav-brand">
        <a href="{{ route('home') }}">
            <img src="{{ mix('/img/logo-new.png') }}" height="100" />
        </a>
    </div>

    @if (Auth::guest())
        <div style="margin: 1em">
            <p>Hackspace Manchester is a place for people who make things to make things.</p>
            <h3>Do we know eachother?</h3>
            @foreach ($sidebarItems[0] ?? [] as $navItem)
                <li class="{{ $loop->first ? 'topNavItem right' : 'topNavItem' }}">
                    <a href="{{ $navItem['href'] }}">{{ $navItem['label'] }}</a>
                </li>
            @endforeach
        </div>
    @elseif (Auth::user()->online_only)
        <div class="sidenav-section">
            <span class="label label-warning">Online Only</span>
        </div>
    @elseif (Auth::user()->status != 'active')
        <div class="sidenav-section">
            @include('partials.components.status-label', ['status' => Auth::user()->status])
        </div>
    @endif

    @php
        $sidebarItems = (new \BB\Services\SidebarItems())->getItems();
    @endphp

    @foreach ($sidebarItems as $section => $items)
        <ul class="nav">
            @foreach ($items as $navItem)
                @if (isset($navItem['external']) && $navItem['external'])
                    <li><a href="{{ $navItem['href'] }}" target="_blank">{{ $navItem['label'] }}</a></li>
                @else
                    @php
                        $highlight = $navItem['highlight'] ?? false;
                        $badge = $navItem['badge'] ?? null;
                        $href = $navItem['href'];
                    @endphp
                    @include('partials.components.side-nav-link', [
                        'name' => $navItem['label'],
                        'href' => $href,
                        'highlight' => $highlight,
                    ])
                @endif
            @endforeach
        </ul>
    @endforeach
</nav>
