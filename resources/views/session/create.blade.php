@extends('layouts.main')

@section('meta-title')
Login
@stop

@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4"
    style="@if($sso) background: #ffc; @endif">
        {!! Form::open(array('route' => 'session.store', 'class'=>'')) !!}

        @if($sso)
            {!! Form::hidden('sso', $sso) !!}
            {!! Form::hidden('sig', $sig) !!}
        @endif

        <div class="row">
            <div class="col-xs-12">
                @if($sso)
                    <h1>SSO Login</h1>
                    <h4>🔒 Single Sign On for Hackspace Manchester</h4>
                    <div class="alert alert-info">
                        Had an account on the forum but don't have an account on the membership system?<br/>
                        Create an <a href="/online-only">online only</a> account - no need to set up payment.
                    </div>
                @else
                    <h1>Login</h1>
                @endif
            </div>
        </div>

        @if (FlashNotification::hasMessage())
        <div class="alert alert-{{ FlashNotification::getLevel() }} alert-dismissable">
            {{ FlashNotification::getMessage() }}

            @if (FlashNotification::hasDetails())
            <ul>
                @foreach(FlashNotification::getDetails()->all() as $error)
                <li style="list-style-type: none;">{{ $error }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        <div class="row">
            <div class="form-group {{ FlashNotification::hasErrorDetail('email', 'has-error has-feedback') }}">
                <div class="col-xs-12">
                    {!! Form::text('email', null, ['class'=>'form-control', 'placeholder'=>'Email']) !!}
                    {!! FlashNotification::getErrorDetail('email', '<span class="glyphicon glyphicon-remove form-control-feedback"></span>') !!}
                </div>
            </div>
            <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
                <div class="col-xs-12">
                    {!! Form::password('password', ['class'=>'form-control', 'placeholder'=>'Password']) !!}
                    {!! FlashNotification::getErrorDetail('password', '<span class="glyphicon glyphicon-remove form-control-feedback"></span>') !!}
                </div>
            </div>

            <div class="col-xs-12">
                {!! Form::submit('Go', array('class'=>'btn btn-primary btn-block')) !!}
            </div>
        </div>
        <div class="row bottom-links">
            <div class="col-xs-12">
                <a href="{{ route('password-reminder.create') }}">Reset your password</a> |
                <a href="{{ route('register') }}">Become a member</a>
            </div>
        </div>

        {!! Form::close() !!}
    </div>

@stop