@extends('layouts.main')


@section('content')

	<div class="col-sm-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <h1>Hackspace Manchester</h1>
                <p class="lead">
                    Welcome to Hackspace Manchester Membership System
                </p>
                <p>
                        You can use this site to sign up to Hackspace Manchester as well as managing your subscription and various other aspects of your membership.
                    </p>
                <p>
                    <a href="{{ route('register') }}" class="btn btn-primary">Become a member</a>
                </p>
                <p>
                    For more information on Hackspace Manchester please visit <a href="https://www.hacman.org.uk">www.hacman.org.uk</a>
                </p>
                <p>
                Already part of Hackspace Manchester then <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
                </p>
            </div>
        </div>
	</div>

@stop
