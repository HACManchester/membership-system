@extends('layouts.main')

@section('page-title')
    Member Storage 
@stop

@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="well">
                @if ($boxesTaken > 0)
                    <h3>You have the following member storage location</h3>
                    <p>Please make sure an obvious "Do Not Hack" label is on your box (preferably front and back) or each piece of loose material</p>
                    <ul>
                        @foreach($memberBoxes as $box)
                            <h3 class="">
                                {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'js-return-box-form')) !!}
                                {!! Form::hidden('user_id', '') !!}
                                📦 Location {{ $box->location }}<small>(#{{ $box->id }})</small>
                                {!! Form::submit('Return Storage Location', array('class'=>'btn btn-default btn-link btn-sm')) !!}
                                {!! Form::close() !!}
                            </h3>
                        @endforeach
                    </ul>
                    
                    @if ($boxesTaken >= 1)
                        <p>
                            ℹ️ You have claimed your member storage allowance.
                        </p>
                    @endif

                    <p>
                        If your no longer using a space and want to return it, please remove your items,
                        return the box if it was found in the space, and then use the return link above.
                    </p>
                @else
                    <h3>You have not claimed a storage location</h3>
                    <p>To do so, find a storage location in the space that is vacant, note the location ID, and then claim it in the list below.</p>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="well">
                Each member can claim up to 1 storage cube or half a shelf of member storage.
                <br />
                Storage is managed by the <a href="{{ route('groups.show', 'storage') }}">member storage</a> team
            </div>
            
        </div>

    </div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Location</th>
            <th>Member</th>
            <th>Message</th>
            @if (Auth::user()->hasRole('storage'))
            <th>Admin</th>
            @endif
        </tr>
    </thead>
@foreach ($storageBoxes as $box)
    <tbody>
        <tr @if($box->user && !$box->user->active)class="warning"@elseif(!$box->user)class="success"@endif>
            <td>{{ $box->id }}</td>
            <td>{{ $box->location }}</td>
            <td>{{ $box->user->name or 'Available' }}</td>
            <td>
                @if($box->user && !$box->user->active)
                    ⚠️ Member left
                @elseif (($volumeAvailable >= $box->size) && !$box->user)
                    @if ($box->location == "Old Members Storage" || Auth::user()->online_only)
                        ⛔ Not available to be claimed
                    @else
                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-left')) !!}
                        {!! Form::hidden('user_id', Auth::user()->id) !!}
                        {!! Form::submit('Claim', array('class'=>'btn btn-default')) !!}
                        {!! Form::close() !!}
                    @endif
                @endif
            </td>
            @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('storage'))
                <td style="background:repeating-linear-gradient( 45deg, #fafafa, #fafafa 40px, #fff 40px, #fff 80px )">
                    @if($box->user)
                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-left')) !!}
                        {!! Form::hidden('user_id', '') !!}
                        {!! Form::submit('Reclaim', array('class'=>'btn btn-default btn-sm')) !!}
                        {!! Form::close() !!}                        
                    @endif
                
                    {!! Form::open(array('method'=>'POST', 'route' => ['storage_boxes.update', $box->id])) !!}
                    {!! Form::select('user_id', [''=>'Allocate member']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
                    {!! Form::submit('✔️', array('class'=>'btn btn-default btn-xs')) !!}
                    {!! Form::close() !!}
                </td>
            @endif
        </tr>
    </tbody>
@endforeach
</table>


@stop
