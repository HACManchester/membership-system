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
                    Sign up to Hackspace Manchester, managing your subscription, join teams and book inductions.
                </p>
                @if ($guest)
                    <p>
                        <a href="{{ route('register') }}" class="btn btn-primary">Become a member</a>
                    </p>
                    <p>
                        For more information on Hackspace Manchester please visit <a href="https://www.hacman.org.uk">www.hacman.org.uk</a>
                    </p>
                    <p>
                        Already part of Hackspace Manchester then <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
                    </p>
                @else
                    <p>
                        Welcome back!
                    </p>
                @endif
            </div>
        </div>
	</div>

@stop
