<li class="get-started-checklist_item {{ $pass_condition ? 'get-started-checklist_item:checked' : ''}}">
    <div class="get-started-checklist_item_id">
        @if ($pass_condition)
            <span>✅</span>
        @else
            <span>{{ $number }}.</span>
        @endif
    </div>
    <div class="get-started-checklist_item_body">
        <p>
            @if ($pass_condition)
                <strong>{{ $title }}</strong>
            @elseif (isset($block_condition) && $block_condition)
                <strong>
                    <span>{{ $title }}</span>
                    <span>🔒 (requires {{ $block_reason }})</span>
                </strong>
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