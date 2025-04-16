<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1E8C99">
    <title>@yield('meta-title', 'Member System') | Hackspace Manchester</title>

    <link href='https://fonts.googleapis.com/css?family=Asap:400,500,700,100' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="{{ asset('img/favicon/favicon.png') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset("/apple-touch-icon.png") }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset("/favicon-32x32.png") }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset("/favicon-16x16.png") }}">
    <link rel="manifest" href="{{ asset("/site.webmanifest") }}">
    
    @if (config('services.sentry.browser_dsn'))
        <script src="https://js.sentry-cdn.com/{{ config('services.sentry.browser_dsn') }}.min.js" crossorigin="anonymous"></script>
    @endif
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ mix('/js/react-app.js') }}" defer></script>
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
