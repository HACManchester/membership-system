@extends('layouts.main')

@section('page-title')
    Large Project Storage 
@stop

@section('content')

    <div class="row">
    <div class="col-md-6">
            <div class="well">
                <h3>⚠️ Coming Soon - self serve large project storage</h3>
                Large Project Storage is limited in the space and can be found in the front right corner of the space.<br/>
                Only projects which are being worked on can be stored here, and large projects left in the space is a breach of rules.<br/>
                Soon, and all members will be notified when, it'll be neccessary to log what is stored on here. For the moment, print a Do Not Hack (DNH) label and fix it to your item. These can be printed on the registration computer.
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
                        <strong> ⚠️Your large project storage will expire {{ $box->expires_at }} ⚠️</strong>
                    </p>
                @endif

                @if ($boxesTaken < 1)
                    You don't currently have any Large Project Storage registered<br />
                @endif

            </div>
        </div>
       

    </div>


<table class="table">
    <thead>
        <tr>
            <th>Item</th>
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
            <td>Item description</td>
            <td>
                @if($box->user)
                    <a href="{{ route('members.show', [$box->user->id]) }}">{{ $box->user->name }}</a>
                @else

                @endif
            </td>
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