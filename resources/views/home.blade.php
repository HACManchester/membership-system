@extends('layouts.main')


@section('content')

	<div class="col-sm-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="menuToggleButton" style="float: none; padding: none; display: inline-block">
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
                        Here you can sign up to Hackspace Manchester, manage your subscription, join teams, book inductions, and more!
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
                    <div class="well">
                        <p>
                            👋 You're logged in as {{$user->name}}!
                        </p>
                        
                        <a class="btn btn-primary" href="{{ route('account.show', [$user->id]) }}">
                            <i class="material-icons">person</i>
                            Your Membership
                        </a>
                       
                    </div>

                @endif
            </div>
        </div>
	</div>

@stop
