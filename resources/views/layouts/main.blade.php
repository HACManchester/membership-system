<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1E8C99">
    <title>@yield('meta-title', 'Member System') | Hackspace Manchester</title>

    <link href='https://fonts.googleapis.com/css?family=Asap:400,700,100' rel='stylesheet' type='text/css'>
    <link href="{{ mix('/css/application.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('img/favicon/favicon.png') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset("/apple-touch-icon.png") }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset("/favicon-32x32.png") }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset("/favicon-16x16.png") }}">
    <link rel="manifest" href="{{ asset("/site.webmanifest") }}">
    
    @if (config('services.sentry.browser_dsn'))
        <script src="https://js.sentry-cdn.com/{{ config('services.sentry.browser_dsn') }}.min.js" crossorigin="anonymous"></script>
    @endif
    
    <script src="//www.google.com/jsapi"></script>
    <script>var BB = BB || {};</script>
    @if (App::environment() == 'production')
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        @if (Auth::guest())
            ga('create', 'UA-53813063-1', 'auto');
        @else
            ga('create', 'UA-53813063-1', { 'userId': '{{ Auth::user()->id }}', cookieDomain: 'auto' });
        @endif
        ga('send', 'pageview');
    </script>
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="{{ $body_class ?? '' }}">
<style>
    body {
        background-color: white;
        background-image: linear-gradient(180deg, #f5f7fa 30%, #c3cfe2 100%);
        background-attachment: fixed;
    }

    .bodyWrap {
        padding-left: 0 !important;
    }
</style>
@include('partials/main-sidenav')

<div id="bodyWrap">

    <header id="pageTitle">
        <div class="menuToggleButton">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="menu-label">Menu</span>
        </div>
        <div class="titles">
            <span class="hidden-xs">@yield('page-key-image')</span>
            <h1 class="title">@yield('page-title')</h1>
            <div class="pull-right action-buttons">@yield('page-action-buttons')</div>
        </div>

    </header>

    @yield('main-tab-bar')

    <div class="container-fluid">

        @yield('content')

    </div>



</div>

<div class="modalMask"></div>
    
    

    @include('partials/js-data')

    <script src="{{ mix('/js/app.js') }}"></script>

    @include('partials/flash-message')
    
    @yield('footer-js')

</body>
</html>
