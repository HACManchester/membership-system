@extends('layouts.main')

@section('meta-title')
Login To Another Service
@stop

@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4"
        style="border-color:red">
        <div class="row">
            <div class="col-xs-12">
                <h1>⛔</h1>
                <h3>There has been an error.</h3>
                @if(isset($code))
                    <p>Error code {{ $code }}</p>

                    @if($code == '2')
                        <b>You cannot continue as your email address is not verified.</b>
                        <hr/>
                        <b>Verify it first before logging into other services.</b>
                        <p>To verify it, <a href="/account/confirm-email/send">click here to re-send it</a>, then try logging in again.</p>
                        <small>Problems? Email the board or shout out on Telegram</small>
                    @endif
                @endif
            </div>
        </div>
    </div>

@stop