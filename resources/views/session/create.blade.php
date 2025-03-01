@extends('layouts.main')

@section('meta-title')
Login
@stop

@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4" style="@if($sso) background: #ffc; @endif">
        <form action="{{ route('session.store') }}" method="POST">
            @csrf

            @if($sso)
                <input type="hidden" name="sso" value="{{ $sso }}">
                <input type="hidden" name="sig" value="{{ $sig }}">
            @endif

            <div class="row">
                <div class="col-xs-12">
                    @if($sso)
                        <h1>SSO Login</h1>
                        <h4>🔒 Single Sign On for Hackspace Manchester</h4>
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
                                
                    @if($errors->any())
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="row">
                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                    <div class="col-xs-12">
                        <input type="text" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}">
                        @if($errors->has('email'))
                            <span class="help-block">
                                @foreach($errors->get('email') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </span>
                        @endif
                    </div>
                </div>
                <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                    <div class="col-xs-12">
                        <input type="password" name="password" class="form-control" placeholder="Password">
                        @if($errors->has('password'))
                            <span class="help-block">
                                @foreach($errors->get('password') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </span>
                        @endif
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

        </form>
    </div>

@stop