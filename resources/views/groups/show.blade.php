@extends('layouts.main')

@section('meta-title')
    Group: {{ $role->title }}
@stop
@section('page-title')
    Group: {{ $role->title }}
@stop

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-success">
                <div class="panel-heading">
                    About {{ $role->title }} group
                </div>
                <div class="panel-body">
                    <p class="lead">
                        {{ $role->description }}
                    </p>
                    @if( $role->slack_channel)
                    <p class="lead">
                        <a href="{{ $role->slack_channel }}">Telegram channel</a>
                    </p>
                    @endif
                    @if ($role->email_public)
                    <p>
                        Email: <a href="mailto:{{ $role->email_public }}">{{ $role->email_public }}</a>
                    </p>
                    @endif
                </div>
            </div>

            <h4>Group members</h4>
            <div class="list-group">
                @foreach($role->users as $user)
                    <a href="{{ route('members.show', $user->id) }}" class="list-group-item">
                        {!! HTML::memberPhoto($user->profile, $user->hash, 50, '') !!}
                        {{ $user->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

@stop