@php
$direction = (Request::get('direction') == 'asc') ? 'desc' : 'asc';
@endphp

<a href="{{ route('account.index', ['sortBy' => $column, 'direction' => $direction, 'page' => Request::get('page'), 'showLeft' => Request::get('showLeft'), 'filter' => Request::get('filter')]) }}">
    {{ $body }}
</a>
