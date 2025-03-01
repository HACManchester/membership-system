{!! $collection->appends([
    'sortBy'        => Request::get('sortBy'),
    'direction'     => Request::get('direction'),
    'date_filter'   => Request::get('date_filter'),
    'reason_filter' => Request::get('reason_filter'),
    'member_filter' => Request::get('member_filter')
])->links() !!}
