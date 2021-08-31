@extends('layouts.main')

@section('page-title')
    Member Storage 
@stop

@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="well">
                @if ($boxesTaken > 0)
                    You have the following member storage, (please make sure a DNH label is on boxes/loose material)<br />
                    <ul>
                        @foreach($memberBoxes as $box)
                            <li>
                                {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'js-return-box-form')) !!}
                                {!! Form::hidden('user_id', '') !!}
                                Location {{ $box->id }} - {{ $box->size }}
                                {!! Form::submit('Return Box', array('class'=>'btn btn-default btn-link btn-sm')) !!}
                                {!! Form::close() !!}
                            </li>
                        @endforeach
                    </ul>
                    <p>
                    @if ($boxesTaken >= 1)
                        You have claimed your member storage allowance <br />
                    @endif
                        If your no longer using a space and want to return it  please remove your box
                        and return it to the member shelves, you can then use the return link above.
                    </p>
                @endif
                @if ($canPayMore)
                    <p>
                    If you wish to claim @if ($boxesTaken > 0) another @else a @endif box you will need to claim a space by clicking claim now
                    </p>

                    <div class="paymentModule" data-reason="storage-box" data-display-reason="Members Storage" data-button-label="Claim Now" data-methods="balance" data-amount="0"></div>

                @endif
                @if ($moneyAvailable > 0)
                    To claim a box click claim next to the storage space you want below, you should probably make sure it is empty before you do this.
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

@if (Auth::user()->hasRole('storage'))
<div class="well">
    When marking a space as returned please make sure shelf is clear.
</div>
@endif

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Location</th>
            <th>Member</th>
            <th></th>
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
                    Member left - box to be reclaimed
                @elseif (($volumeAvailable >= $box->size) && !$box->user)
                    @if ($box->location == "Old Members Storage" || Auth::user()->online_only)
                        ⛔ Unclaimable - Old storage or you don't have permission
                    @else
                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-form navbar-left')) !!}
                        {!! Form::hidden('user_id', Auth::user()->id) !!}
                        {!! Form::submit('Claim', array('class'=>'btn btn-default')) !!}
                        {!! Form::close() !!}
                    @endif
                @endif
            </td>
            @if (Auth::user()->hasRole('storage'))
                <td>
                    @if($box->user)
                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-form navbar-left')) !!}
                        {!! Form::hidden('user_id', '') !!}
                        {!! Form::submit('Reclaim', array('class'=>'btn btn-default btn-sm')) !!}
                        {!! Form::close() !!}
                    @endif
                </td>
            @endif
        </tr>
    </tbody>
@endforeach
</table>


@stop
