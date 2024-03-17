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

        {{-- Do not use Form::Hidden, as that will pull 'sso' and 'sig' from input, which is not appropriate to use here. --}}
        <input type="hidden" name="sso" value="{{ $sso }}">
        <input type="hidden" name="sig" value="{{ $sig }}">

        <div class="row">
            <div class="col-xs-12">
                <h3>Confirm you're happy to log into another service</h3>
            </div>
        </div>

        <div class="alert alert-success text-left">
            🔒 Part of Hackspace Manchester<br/>
            ↪️ {{ parse_url($return_sso_url, PHP_URL_HOST) }}<br/>
            ✉️ {{ $user->email }}.<br/>
        </div>

        <small>
             ℹ️ Not the same email? You may continue and this can later be harmonised by reaching out to an admin or board member on Telegram.
        </small>

        <br/>

        <div class="row">
            {!! Form::submit('Continue', array('class'=>'btn btn-primary')) !!}
        </div>

        {!! Form::close() !!}
    </div>

@stop