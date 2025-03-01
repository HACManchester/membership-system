@extends('layouts.main')

@section('meta-title')
Member List
@stop

@section('page-title')
Members
@stop

@section('page-action-buttons')
    <!--<a class="btn btn-secondary" href="{{ route('account.create') }}">Create a new Member</a>-->
    <a class="btn btn-secondary" href="{{ route('notificationemail.create') }}">Email Members</a>
@stop

@section('main-tab-bar')
<nav id="mainTabBar">
    <ul role="tablist">
        <li class="@if (Request::get('showLeft', 0) == '0') active @endif">
            <a href="{{ route('account.index', ['showLeft' => 0]) }}">Active Members</a>
        </li>
        <li class="@if (Request::get('showLeft', 0) == 1) active @endif">
            <a href="{{ route('account.index', ['showLeft' => 1]) }}">Old Members</a>
        </li>
    </ul>
</nav>
@stop

@section('content')
<div class="well">
    <h3>Search Tools</h3>
    <form>
        <div class="row">
            <div class="col-md-6">
                <input name="filter" class="form-control" value="{{ Request::get('filter') }}" placeholder="Filter by name, email, username..., or exact keyfob"/>
                <label for="include_online_only">Include online only accounts?</label>
                <input type="checkbox" name="include_online_only" value="1"/>
                <br/>
                <label for="include_online_only">New Members only (14d)</label>
                <input type="checkbox" name="new_only" value="1"/>
                <br/>
                <input type="submit" class="btn btn-info form-control"/><br/>
            </div>
            <div class="col-md-6">
                <b>Number of records returned: {!! count($users) !!}</b>
                <i>If using a mobile device, use horizontally or use desktop mode for more information.</i>
            </div>
        </div>
    </form>
    @include('partials.components.user-paginator-links', ['collection' => $users])
</div>
<table class="table memberList">
    <thead>
        <tr>
            <th></th>
            <th>@include('partials.components.sort-users-by', ['column' => 'name', 'body' => 'Name'])</th>
            <th>@include('partials.components.sort-users-by', ['column' => 'status', 'body' => 'Status'])</th>
            <th class="hidden-xs">@include('partials.components.sort-users-by', ['column' => 'key_holder', 'body' => 'Key Holder'])</th>
            <th class="hidden-xs">@include('partials.components.sort-users-by', ['column' => 'trusted', 'body' => 'Trusted'])</th>
            <th class="hidden-xs">@include('partials.components.sort-users-by', ['column' => 'created_at', 'body' => 'Created'])/@include('partials.components.sort-users-by', ['column' => 'seen_at', 'body' => 'Seen'])</th>
        </tr>
    </thead>
    <tbody>
        @each('account.index-row', $users, 'user')
    </tbody>
</table>
@include('partials.components.user-paginator-links', ['collection' => $users])
@stop