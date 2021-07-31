@extends('layouts.main')

@section('page-title')
    Access Codes 
@stop

@section('content')
@if (Auth::user()->hasRole('admin'))
<div class="well">
    Any change here may take up to 5 minutes to update on the door.<br/>
    If the space has lost internet then the changes won't be made until the device somes back online.
</div>
@endif


@foreach ($accessCodes as $accessCode)
    <div class="row">
        <div class="well well-lg {{ $accessCode->enabled ? 'enabled' : 'disabled' }}">
            <div class="row">
                <div class="col-md-8">
                    <h2>{{ $accessCode->name }}</h2>
                    <h1>{{ $accessCode->code }}</h1>
                </div>
                <div class="col-md-4">
                    <i class="material-icons">check</i>
                    @if (Auth::user()->isAdmin())
                        {!! Form::open(array('method'=>'POST', 'class'=>'', 'route' => ['access-codes.index', $accessCode->id])) !!}
                            {!! Form::hidden('enabled', !$accessCode->enabled) !!}
                            {!! Form::submit($accessCode->enabled ? 'Disable' : 'Enable', array('class'=>'btn btn-default')) !!}
                        {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach


@stop