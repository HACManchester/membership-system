@extends('layouts.main')

@section('content')
<div class="col-sm-12">
    <div class="row">
            <h1>Hackspace Manchester - Admin Area</h1>
            <hr/>

            <div class="col-md-6 well">
                <h4>Manage Members</h4>
                <ul>
                    <li><a href="/account">Search, find, view accounts</a></li>
                    <li><a href="/account?sortBy=seen_at&direction=desc&limit=20">Recent Members</a></li>
                </ul>
            </div>
            <div class="col-md-6 well">
                <h4>View Logs</h4>
                <ul>
                    <li><a href="/logs">See what's been going on</a></li>
                </ul>
            </div>
            <div class="col-md-6 well">
                <h4>Manage Roles & Teams</h4>
                <ul>
                    <li><a href="/roles">Move people in and out of roles.</a></li>
                </ul>
            </div>
            <div class="col-md-6 well">
                <h4>Inductions</h4>
                <ul>
                    <li><a href="/member_inductions">Who has completed general induction</a></li>
                </ul>
            </div>
            <div class="col-md-6 well">
                <h4>Payments</h4>
                <ul>
                    <li><a href="/payments">All payments</a></li>
                    <li><a href="/payments/sub-charges">Subscription Charges</a></li>
                </ul>
            </div>
            <div class="col-md-6 well">
                <h4>Activity</h4>
                <ul>
                    <li><a href="/activity">All Activity</a></li>
                    <li><a href="/activity/realtime">Realtime</a></li>
                </ul>
            </div>
        </div>
	</div>
@stop
