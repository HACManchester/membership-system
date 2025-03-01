@php
$direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
@endphp

<a href="{{ route($route, ['sortBy' => $column, 'direction' => $direction, 'page' => Request::get('page'), 'date_filter' => Request::get('date_filter'), 'member_filter' => Request::get('member_filter'), 'reason_filter' => Request::get('reason_filter')]) }}">
    {{ $body }}
</a>
