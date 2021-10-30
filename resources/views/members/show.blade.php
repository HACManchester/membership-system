@extends('layouts.main')

@section('meta-title')
{{ $user->name }}
@stop

@section('page-title')
{{ $user->name }}
@stop

@section('page-action-buttons')
    @if (!@Auth::guest() && $user->id == Auth::user()->id)
    <a class="btn btn-secondary" href="{{ route('account.profile.edit', $user->id) }}"><span class="glyphicon glyphicon-pencil"></span> Edit Profile</a>
    @endif

    @if (!@Auth::guest() && ($user->id == Auth::user()->id || Auth::user()->hasRole('admin')))
    <a class="btn btn-info" href="{{ route('account.show', $user->id) }}"><span class="glyphicon glyphicon-user"></span> Member Account</a>
    @endif
@stop

@section('content')
<div class="row memberProfile">
    <div class="col-sm-12 col-md-10 col-md-offset-1">

        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-4">

                {!! HTML::memberPhoto($profileData, $user->hash) !!}
                
            </div>
            <div class="col-xs-12 col-sm-6 col-md-8 pull-right">
                <h3>{{ $profileData->present()->tagline }}</h3>
                <p class="lead">
                    {{ $profileData->present()->description }}
                </p>
                <ul>
                    {!! HTML::profileSocialMediaListItem('GitHub', $profileData->present()->gitHubLink) !!}
                    {!! HTML::profileSocialMediaListItem('Twitter', $profileData->present()->twitterLink) !!}
                    {!! HTML::profileSocialMediaListItem('Telegram', $profileData->present()->googlePlusLink) !!}
                    {!! HTML::profileSocialMediaListItem('Facebook', $profileData->present()->facebookLink) !!}
                    {!! HTML::profileSocialMediaListItem('Website', $profileData->present()->website) !!}
                    @if ($profileData->irc)
                    <li>IRC - <a href="https://t.me/HACManchester">https://t.me/HACManchester</a> - {{ $profileData->irc }}</li>
                    @endif
                </ul>
            </div>
        </div>

        @if (count($userSkills) > 0)
        <style>
            .skill-box:nth-child(4n) .thumbnail {
                background-color: #4158D0;
                background-image: linear-gradient(43deg, #4158D0 0%, #C850C0 46%, #FFCC70 100%);
            }
            .skill-box:nth-child(4n + 1) .thumbnail {
                background-image: linear-gradient( 89.9deg,  rgba(255,243,110,1) 16.9%, rgba(30,204,214,1) 55.1%, rgba(5,54,154,1) 90.7% );
            }
            .skill-box:nth-child(4n + 2) .thumbnail {
                background-image: linear-gradient( 109.6deg,  rgba(247,253,166,1) 11.2%, rgba(128,255,221,1) 57.8%, rgba(255,128,249,1) 85.9% );
            }
            .skill-box:nth-child(4n + 4) .thumbnail {
                background-image: linear-gradient( 68.2deg,  rgba(255,202,88,1) 0%, rgba(139,73,255,1) 100.2% );
            }

           

        </style>
            <div class="row">
                <div class="col-xs-12">
                    <h3>Skills</h3>
                    <div class="skill-list" style="display: grid; grid-template-columns: 33% 33% 33%;">
                        @foreach($userSkills as $skill)
                            <div class="skill-box">
                                <div class="thumbnail">
                                    <img src="/img/skills/{{  $skill['icon'] }}" width="100" height="100" />
                                    <div class="caption">
                                        <h3>{{  $skill['name'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        @endif
    </div>

</div>
@stop
