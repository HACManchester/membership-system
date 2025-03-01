@if ($name && $url)
    <li>{{ $name }} - <a href="{{ $url }}" title="{{ $name }}">{{ $url }}</a></li>
@endif
