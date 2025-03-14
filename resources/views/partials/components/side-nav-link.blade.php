@php
$style = $highlight ?? false ? "background:yellow;" : "";
@endphp

<li><a style="{{ $style }}" href="{{ $href }}">{{ $name }}</a></li>
