<li class="get-started-checklist_item {{ $condition ? 'get-started-checklist_item:checked' : ''}}">
    <div class="get-started-checklist_item_id"><span>{{ $condition ? '✅' : "{$number}."}}</span></div>
    <div class="get-started-checklist_item_body">
        <p>
            @if ($condition)
            <strong>{{ $title }}</strong>
            @else
            <a href="{{ $link }}" target="{{ $link_target ?? '_self' }}" rel="noopener"><strong>{{ $title }}</strong></a>
            @endif
        </p>
        @if (isset($rawDescription))
            {!! $rawDescription !!}
        @else
            <p>{{ $description }}</p>
        @endif
    </div>
</li>