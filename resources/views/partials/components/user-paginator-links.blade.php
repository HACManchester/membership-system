{!! $collection->appends([
    'sortBy'    => Request::get('sortBy'),
    'direction' => Request::get('direction'),
    'showLeft'  => Request::get('showLeft'),
    'filter'    => Request::get('filter')
])->links() !!}
