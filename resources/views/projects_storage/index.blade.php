@extends('layouts.main')

@section('page-title')
    Large Project Storage 
@stop

@section('content')

    <div class="row">
    <div class="col-md-6">
            <div class="well">
                Large Project Storage is limited in the Space. Please check to see if there is space available before leaving projects in the Space. <br> <br>
                Anyone leaving a large project outwith of Large Project Storage will be deemed to be in breach of the Members Storage Rules and appropirate action will be taken 
                <br />
            </div>
            
        </div>
        <div class="col-md-6">
            <div class="well">
                @if ($boxesTaken > 0)
                    You have the following Large Project storage, (please make sure a DNH label is on boxes/loose material)<br />
                    <ul>
                        @foreach($memberBoxes as $box)
                            <li>
                                {!! Form::open(array('method'=>'PUT', 'route' => ['projects_storage.update', $box->id], 'class'=>'js-return-box-form')) !!}
                                {!! Form::hidden('user_id', '') !!}
                                Location {{ $box->id }} - Large
                                {!! Form::submit('Return Space', array('class'=>'btn btn-default btn-link btn-sm')) !!}
                                {!! Form::close() !!}
                            </li>
                        @endforeach
                        
                    </ul>
                    <p>
                    @if ($boxesTaken >= 1)
                        You have claimed your maximum Large Project storage allowance <br />
                    @endif
                                           <strong> Your large project storage will expire {{ $box->expires_at }} .</strong>
                    </p>
                @endif

                @if ($boxesTaken < 1)
                    You don't currently have any Large Project Storage registered<br />
                          
                    <p>
                    
                @endif
                
                @if ($canPayMore)
                    <p>
                    If you wish to claim @if ($boxesTaken > 0) another @else a @endif box you will need to claim a space by clicking claim now
                    </p>

                    <div class="paymentModule" data-reason="storage-box" data-display-reason="Large Project Storage" data-button-label="Claim Now" data-methods="balance" data-amount="0"></div>

                @endif
                @if ($moneyAvailable > 0)
                    To claim a box click claim next to the storage space you want below, you should probably make sure it is empty before you do this.
                @endif

            </div>
        </div>
       

    </div>

@if (Auth::user()->hasRole('storage'))
<div class="well">
    When marking an area as free please make sure space is clear
</div>
@endif

<table class="table">
    <thead>
        <tr>
            <th>Location ID</th>
            <th>Storage</th>
            <th>Member</th>
            <th>Expires</th>
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
            <td>Large</td>
            <td>{{ $box->user->name or 'Available' }}</td>
            <td>{{ $box->expires_at}}</td>
            <td>
                @if($box->user && !$box->user->active)
                    Member left - box to be reclaimed
                @elseif (($volumeAvailable >= $box->size) && !$box->user)
                    {!! Form::open(array('method'=>'PUT', 'route' => ['projects_storage.update', $box->id], 'class'=>'navbar-form navbar-left')) !!}
                    {!! Form::hidden('user_id', Auth::user()->id) !!}
                    {!! Form::submit('Claim', array('class'=>'btn btn-default')) !!}
                    {!! Form::close() !!}
                @endif
            </td>
            @if (Auth::user()->hasRole('storage'))
                <td>
                    @if($box->user)
                        {!! Form::open(array('method'=>'PUT', 'route' => ['projects_storage.update', $box->id], 'class'=>'navbar-form navbar-left')) !!}
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