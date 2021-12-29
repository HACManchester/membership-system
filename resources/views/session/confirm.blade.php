@extends('layouts.main')

@section('meta-title')
Login To Another Service
@stop

@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
        {!! Form::open(array('route' => 'session.store', 'class'=>'')) !!}

        <div class="row">
            <div class="col-xs-12">
                <h1>You're about to login to another service</h1>
            </div>
        </div>

        
        <div class="alert alert-{{ Notification::getLevel() }} alert-dismissable">
            The service you're logging into is part of Hackspace Manchester
        </div>

        <div class="row">
        </div>

        {!! Form::close() !!}
    </div>

@stop