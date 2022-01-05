@extends('layouts.main')

@section('content')
	<div class="col-sm-12">
        <div class="row">
            <div class="col-md-6 well">
                <h4>Manage Members</h4>
                <a href="/account">Search, find, view accounts</a>
            </div>
            <div class="col-md-6 well">
                <h4>View Logs</h4>
                <a href="/logs">See what's been going on</a>
            </div>
            <div class="col-md-6 well">
                <h4>Manage Roles & Teams</h4>
                <a href="/roles">Move people in and out of roles.</a>
            </div>
            <div class="col-md-6 well">
                <h4>Inductions</h4>
                <a href="/member_inductions">Who has completed general induction</a>
            </div>
            <div class="col-md-6 well">
                <h4>Payments</h4>
                <a href="/payments">All payments</a>
                <a href="/payments/sub-charges">Subscription Charges</a>
            </div>
            <div class="col-md-6 well">
                <h4>Activity</h4>
                <a href="/activity">All Activity</a>
                <a href="/activity/realtime">Realtime</a>
            </div>
        </div>
        <div class="panel panel-default" style="opacity:0.95;box-shadow:0 0 40px white;">
            <div class="panel-body">
                <h1>Hackspace Manchester - Admin Area</h1>
                
                <div class="alert alert-success">
                    <p>
                        <h3>A gift for you awaits!</h3>
                        <p>Enter your gift code below, and you'll be able to register your account</p>
                        {!! Form::open(array('route' => 'register', 'method'=>'GET' ,'class'=>'form-horizontal')) !!}
                        {!! Form::hidden('gift_certificate', '1') !!}
                        {!! Form::text('gift_code', null, ['class'=>'form-control', 'required' => 'required', 'placeholder' => 'XXX-YYY-ZZZ']) !!}
                        {!! Form::submit('Claim your gift!', array('class'=>'btn btn-primary')) !!}
                        {!! Form::close() !!}
                    </p>
                </div>

            </div>
        </div>
	</div>
@stop
