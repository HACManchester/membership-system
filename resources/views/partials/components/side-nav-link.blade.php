@php
$style = $highlight ?? false ? "background:yellow;" : "";
$routeParams = $routeParams ?? [];
@endphp

<li><a style="{{ $style }}" href="{{ route($route, $routeParams) }}">{{ $name }}</a></li>
