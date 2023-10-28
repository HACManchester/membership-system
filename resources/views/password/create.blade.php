@extends('layouts.main')

@section('page-title')
    Password Reset
@stop


@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
        {!! Form::open(array('route' => 'password-reminder.store', 'class'=>'')) !!}

        <div class="row">
            <div class="col-xs-12">
                <h1>Password Reset</h1>
                <p>
                    Forgotten your password or is it just not working?<br />
                    Enter your email address here and watch out for the reset email
                </p>
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
                    {!! Form::input('email', 'email', null, ['class'=>'form-control', 'placeholder'=>'Email']) !!}
                    {!! FlashNotification::getErrorDetail('email', '<span class="glyphicon glyphicon-remove form-control-feedback"></span>') !!}
                </div>
            </div>

            <div class="col-xs-12">
                {!! Form::submit('Go', array('class'=>'btn btn-primary btn-block')) !!}
            </div>
        </div>
        <div class="row bottom-links">
            <div class="col-xs-12">
                <a href="{{ route('login') }}">Login</a> |
                <a href="{{ route('register') }}">Become a member</a>
            </div>
        </div>

        {!! Form::close() !!}
    </div>

@stop