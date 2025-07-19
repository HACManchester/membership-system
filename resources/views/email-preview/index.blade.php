@extends('layouts.main')

@section('meta-title')
Email & Notification Previews
@stop

@section('page-title')
Email & Notification Previews
@stop

@section('content')
<div class="well">
    <p>Preview all email templates and notifications used in the system. These previews use dummy data to show how messages will appear to recipients.</p>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Email Templates</h3>
    </div>
    <div class="panel-body">
        <div class="list-group">
            @foreach($emails as $name => $route)
                <a href="{{ $route }}" class="list-group-item" target="_blank">
                    <h4 class="list-group-item-heading">{{ $name }}</h4>
                    <p class="list-group-item-text">Click to preview this email template</p>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Notification Templates</h3>
    </div>
    <div class="panel-body">
        <div class="list-group">
            @foreach($notifications as $name => $route)
                <a href="{{ $route }}" class="list-group-item" target="_blank">
                    <h4 class="list-group-item-heading">{{ $name }}</h4>
                    <p class="list-group-item-text">Click to preview this notification</p>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="alert alert-info">
    <strong>Note:</strong> These previews open in a new tab and show the actual content that would be sent to users.
</div>
@stop