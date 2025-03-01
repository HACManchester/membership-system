@extends('layouts.main')

@section('page-title')
    Password Reset
@stop


@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
        <form action="{{ route('password.reset.complete') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-xs-12">
                    <h1>Set a new Password</h1>
                    <p>
                        Enter your email address and choose a new password for your account
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
                        <input type="text" name="email" class="form-control" placeholder="Email" value="{{ $email }}">
                        {!! FlashNotification::getErrorDetail('email', '<span class="glyphicon glyphicon-remove form-control-feedback"></span>') !!}
                    </div>
                </div>
                <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
                    <div class="col-xs-12">
                        <input type="password" name="password" class="form-control" placeholder="Password">
                        {!! FlashNotification::getErrorDetail('password', '<span class="glyphicon glyphicon-remove form-control-feedback"></span>') !!}
                    </div>
                </div>

                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary btn-block">Go</button>
                </div>
            </div>
            <div class="row bottom-links">
                <div class="col-xs-12">
                    <a href="{{ route('password-reminder.create') }}">Reset your password</a> |
                    <a href="{{ route('register') }}">Become a member</a>
                </div>
            </div>
            <input type="hidden" name="token" value="{{ $token }}">
        </form>
    </div>

@stop