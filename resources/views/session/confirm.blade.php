@extends('layouts.main')

@section('meta-title')
Login To Another Service
@stop

@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4"
        style="border-color: yellow">
        {!! Form::open(array(
            'url' => $return_sso_url, 
            'method' => 'GET'
        )) !!}

        {!! Form::hidden('sso', $sso) !!}
        {!! Form::hidden('sig', $sig) !!}

        <div class="row">
            <div class="col-xs-12">
                <h3>Confirm you're happy to log into another service</h3>
            </div>
        </div>

        <div class="alert alert-success text-left">
            🔒 A Hackspace Manchester site.<br/>
            ✉️ You'll be logged in under {{ $user->email }}.<br/>
            <small>
                Not the same email as on the forum? This can be harmonised by reaching out to an admin or board member on Telegram.
            </small>
        </div>

        <p>
            Continuing will redirect you back to `list.hacman.org.uk`.
        </p>
        <div class="row">
            {!! Form::submit('Continue', array('class'=>'btn btn-primary')) !!}
        </div>

        {!! Form::close() !!}
    </div>

@stop