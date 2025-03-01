@extends('layouts.main')

@section('meta-title')
{{ $user->name }}
@stop

@section('page-title')
    {{ $user->name }}
    @if (trim($user->pronouns))
        <em>({{ $user->pronouns }})</em>
    @endif
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
                @include('partials.components.member-photo', ['profileData' => $profileData, 'userHash' => $user->hash])
            </div>
            <div class="col-xs-12 col-sm-6 col-md-8 pull-right">
                <h3 style="border-bottom: 1px solid black; padding-bottom: 5px;">{{ $profileData->present()->tagline }}</h3>
                <p class="lead">
                    {{ $profileData->present()->description }}
                </p>
                <ul>
                    @include('partials.components.profile-social-media-list-item', ['name' => 'GitHub', 'url' => $profileData->present()->gitHubLink])
                    @include('partials.components.profile-social-media-list-item', ['name' => 'Twitter', 'url' => $profileData->present()->twitterLink])
                    @include('partials.components.profile-social-media-list-item', ['name' => 'Telegram', 'url' => $profileData->present()->googlePlusLink])
                    @include('partials.components.profile-social-media-list-item', ['name' => 'Facebook', 'url' => $profileData->present()->facebookLink])
                    @include('partials.components.profile-social-media-list-item', ['name' => 'Website', 'url' => $profileData->present()->website])
                    @if ($profileData->irc)
                    <li>IRC - <a href="https://t.me/HACManchester">https://t.me/HACManchester</a> - {{ $profileData->irc }}</li>
                    @endif
                </ul>
            </div>
        </div>

        @if (count($userSkills) > 0)
        <style>
            .skill-box:nth-child(5n + 1) .thumbnail {
                background-image: linear-gradient( 179.4deg,  rgba(33,150,243,1) 1.8%, rgba(22,255,245,0.60) 97.1% );
            }
            .skill-box:nth-child(5n + 2) .thumbnail {
                background-image: radial-gradient( circle farthest-corner at 5.3% 17.2%,  rgba(255,208,253,1) 0%, rgba(255,237,216,1) 90% );
            }
            .skill-box:nth-child(5n + 3) .thumbnail {
                background-image: linear-gradient( 109.6deg,  rgba(255,207,84,1) 11.2%, rgba(255,158,27,1) 91.1% );
            }
            .skill-box:nth-child(5n + 4) .thumbnail {
                background-image: linear-gradient( 109.6deg,  rgba(255,219,47,1) 11.2%, rgba(244,253,0,1) 100.2% );
            }
            .skill-box:nth-child(5n + 5) .thumbnail {
                background-image: linear-gradient(to top, #fad0c4 0%, #ffd1ff 100%);
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
