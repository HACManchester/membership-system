@extends('layouts.main')

@section('meta-title')
Hackspace Manchester Members
@stop

@section('page-title')
Members
@stop


@section('content')

<div class="memberGrid">
    <div class="row">
        @foreach ($users as $user)
        <div class="col-xs-6 col-md-3 col-lg-2">
            <div class="memberBlock" style="border: 5px solid #fff000">
                <a href="{{ route('members.show', $user->user_id) }}">
                    {!! HTML::memberPhoto(
                        (object)[
                            "profile_photo"=>$user->profile_photo,
                            "profile_photo_private"=>$user->profile_photo_private
                        ], 
                        $user->hash, 
                        200) 
                    !!}
                    <div class="memberDetails">
                        <strong>{{ $user->display_name }}</strong>
                    </div>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@stop