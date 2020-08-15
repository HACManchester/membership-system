@extends('layouts.main')

@section('meta-title')
Activity Log
@stop

@section('page-title')
Activity Log
@stop

@section('content')

<div class="page-header">
    <h3>Activity in the main space - {{ $date->format('l jS \\of F') }}</h3>


    <div class="well well-sm">
        <p>
            Latest Activity from the Space includng door entry and if certain tools are in use 
        </p>
            @if (!$doorPin)
            <p>
                @if (Auth::user()->isAdmin())
                    <br>
                    As an administrator you can update the displayed blue door code on the account page using the form below 
                    {!! Form::open(['route'=> 'settings.update', 'method'=>'POST', 'class'=>'form-inline']) !!}
                    <input type="text" maxlength="10" name="value" value="" placeholder="New code" class="form-control">
                    <input type="hidden" name="key" value="emergency_door_key_storage_pin">
                    <input type="submit" value="Update door key" class="btn btn-info">
                    {!! Form::close() !!}
                @endif
            </p>
        @endif
        @if ($doorPin)
            <p>
                <strong>{{ $doorPin }}</strong><br>
                Make sure you return the key once the door has been opened.
            </p>
        @endif
    </div>


    {!! Form::open(['route'=> 'activity.index', 'method'=>'GET', 'id'=>'activityDatePicker', 'class'=>'form-inline']) !!}
    <div class="input-group date">
        <input name="date" type="text" class="form-control js-date-select" value="{{ $date->format('Y-m-d') }}">
    </div>
    {!! Form::close() !!}

    <ul class="pager">
    @if ($previousDate)
        <li class="previous">{!! link_to_route('activity.index', $previousDate->format('d/m/y'). ' &larr; Previous', ['date'=>$previousDate->format('Y-m-d')]) !!}</li>
    @else
        <li class="previous disabled"><a href="#">Previous</a></li>
    @endif
    @if ($nextDate)
        <li class="next">{!! link_to_route('activity.index', 'Next &rarr; '.$nextDate->format('d/m/y'), ['date'=>$nextDate->format('Y-m-d')]) !!}</li>
    @else
        <li class="next disabled"><a href="#">Next</a></li>
    @endif
    </ul>
</div>

<div class="memberGrid">
    <div class="row">
        @foreach ($logEntries as $logEntry)
        <div class="col-sm-6 col-md-4 col-lg-2">
            <div class="memberBlock">

                {!! HTML::memberPhoto($logEntry->user->profile, $logEntry->user->hash, 200) !!}

                <div class="memberDetails">
                    <strong>{{ $logEntry->user->name }}</strong><br />
                    <strong>{{ $logEntry->service }}</strong><br />
                    @if ($logEntry->delayed)
                        <span data-toggle="tooltip" data-placement="below" title="This record doesn't have an accurate time">(delayed)</span>
                    @else
                        {{ $logEntry->created_at->toTimeString() }}
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>
</div>

@stop
