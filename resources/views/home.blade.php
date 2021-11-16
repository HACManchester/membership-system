@extends('layouts.main')


@section('content')

	<div class="col-sm-12 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2">
        <div class="panel panel-default" style="opacity:0.95;box-shadow:0 0 40px white;">
            <div class="panel-body">
                <div class="menuToggleButton" style="float: none; padding: 0">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="menu-label">Menu</span>
                </div>
                <h1>Hackspace Manchester</h1>
                <p class="lead">
                    Welcome to Hackspace Manchester Membership System
                </p>
                @if ($guest)
                    <p>
                        Here you can:
                        <ul>
                            <li>Sign up to Hackspace Manchester</li>
                            <li>Manage your membership</li>
                            <li>Book tool inductions</li>
                            <li>Join teams</li>
                            <li>... and more!</li>
                        </ul>
                    </p>
                    <p>
                        For more information on Hackspace Manchester please visit <a href="https://www.hacman.org.uk">www.hacman.org.uk</a>
                    </p>
                   
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="well">
                                <h4>Ready to join?</h4>
                                <a href="https://members.hacman.org.uk/register" class="btn btn-primary">✨ Become a member</a>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="well">
                                <h4>Already a member?</h4>
                                <a href="https://members.hacman.org.uk/login" class="btn btn-secondary">🔑 Log in</a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-success">
                        <p>
                            <h3>👋 Welcome back, <a href="{{ route('account.show', [$user->id]) }}">{{$user->name}}</a></h3>
                            <p>
                                You're logged in.
                            </p>
                        </p>
                        
                        <div class="alert alert-info">
                            <h4>Quick Links</h4>
                            <a href="/equipment" class="btn btn-info">Tools &amp; Equipment</a>
                            <a href="/account/0/edit" class="btn btn-warning">Keyfobs &amp; Access</a>
                            <a href="/account/0/balance" class="btn btn-success">Balance</a>
                        </div>
                       
                    </div>

                @endif
            </div>
        </div>
	</div>

@stop
