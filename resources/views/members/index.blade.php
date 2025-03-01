@extends('layouts.main')

@section('meta-title')
Hackspace Manchester Members
@stop

@section('page-title')
Members
@stop


@section('content')
<style>
    .memberBlock img{
        border-radius: 1em;
    }
</style>
<div class="memberGrid">
    <div class="row">
        @foreach ($users as $user)
        <div class="col-xs-6 col-md-3 col-lg-2">
            <div class="memberBlock" style="border: 5px solid #000; border-radius: 1em; background: black;">
                <a href="{{ route('members.show', $user->user_id) }}">
                    @include('partials.components.member-photo', [
                        'profileData' => (object)[
                            "profile_photo"=>$user->profile_photo,
                            "profile_photo_private"=>$user->profile_photo_private
                        ],
                        'userHash' => $user->hash,
                        'size' => 200
                    ])
                    <div class="memberDetails" style="color:white;">
                        <strong>{{ $user->display_name }}</strong>
                    </div>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@stop