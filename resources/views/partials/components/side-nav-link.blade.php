@php
    $style = $highlight ?? false ? 'background:yellow;' : '';
@endphp

<li>
    <a style="{{ $style }}" href="{{ $href }}">
        <span>{{ $name }}</span>
        @if ($badge)
            <span class="badge">{{ $badge }}</span>
        @endif
    </a>
</li>
