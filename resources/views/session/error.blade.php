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
                <h3>Process aborted.</h3>
                @if(isset($code))
                    <p>Error code {{ $code }}</p>

                    @if($code == '2')
                        <b>Your email address is not verified. Verify it first before logging into other services.</b>
                        <p>To verify it, click "Your Membership" in the left-hand menu, then in the notification, click the link to re-send the verification email</p>
                        <p>Problems? Email the board or shout out on Telegram</p>
                    @endif
                @endif
            </div>
        </div>
    </div>

@stop