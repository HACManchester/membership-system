@extends('layouts.main')

@section('meta-title')
Fill in your profile
@stop

@section('page-title')
Fill in your profile
@stop

@section('content')
<div class="col-xs-12 col-md-8 col-md-offset-2">

<div class="page-header">
    <p>This information will be shared with others, enter as much or as little as you want</p>
</div>

<form action="{{ route('account.profile.update', $userId) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('skills', 'has-error has-feedback') }}">
                <label for="skills">Skills</label>
                <select name="skills[]" id="skills" class="form-control js-advanced-dropdown" multiple>
                    @foreach($skills as $id => $name)
                        <option value="{{ $id }}" 
                            @if(old('skills') && is_array(old('skills')))
                                {{ in_array($id, old('skills')) ? 'selected' : '' }}
                            @elseif(isset($profileData) && $profileData->skills)
                                {{ in_array($id, $profileData->skills) ? 'selected' : '' }}
                            @endif
                        >{{ $name }}</option>
                    @endforeach
                </select>
                {!! FlashNotification::getErrorDetail('skills') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('tagline', 'has-error has-feedback') }}">
                <label for="tagline">Tagline</label>
                <input type="text" name="tagline" id="tagline" class="form-control" value="{{ old('tagline', $profileData->tagline) }}">
                {!! FlashNotification::getErrorDetail('tagline') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('description', 'has-error has-feedback') }}">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ old('description', $profileData->description) }}</textarea>
                {!! FlashNotification::getErrorDetail('description') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('twitter', 'has-error has-feedback') }}">
                <label for="twitter">Twitter</label>
                <div class="input-group">
                    <div class="input-group-addon">https://twitter.com/</div>
                    <input type="text" name="twitter" id="twitter" class="form-control" value="{{ old('twitter', $profileData->twitter) }}">
                </div>
                {!! FlashNotification::getErrorDetail('twitter') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('facebook', 'has-error has-feedback') }}">
                <label for="facebook">Facebook</label>
                <div class="input-group">
                    <div class="input-group-addon">https://www.facebook.com/</div>
                    <input type="text" name="facebook" id="facebook" class="form-control" value="{{ old('facebook', $profileData->facebook) }}">
                </div>
                {!! FlashNotification::getErrorDetail('facebook') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('google_plus', 'has-error has-feedback') }}">
                <label for="google_plus">Telegram</label>
                <div class="input-group">
                    <div class="input-group-addon">Your Telegram Username</div>
                    <input type="text" name="google_plus" id="google_plus" class="form-control" value="{{ old('google_plus', $profileData->google_plus) }}">
                </div>
                {!! FlashNotification::getErrorDetail('google_plus') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('github', 'has-error has-feedback') }}">
                <label for="github">GitHub</label>
                <div class="input-group">
                    <div class="input-group-addon">https://github.com/</div>
                    <input type="text" name="github" id="github" class="form-control" value="{{ old('github', $profileData->github) }}">
                </div>
                {!! FlashNotification::getErrorDetail('github') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('website', 'has-error has-feedback') }}">
                <label for="website">Website</label>
                <input type="text" name="website" id="website" class="form-control" value="{{ old('website', $profileData->website) }}">
                {!! FlashNotification::getErrorDetail('website') !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('irc', 'has-error has-feedback') }}">
                <label for="irc">IRC</label>
                <input type="text" name="irc" id="irc" class="form-control" value="{{ old('irc', $profileData->irc) }}">
                {!! FlashNotification::getErrorDetail('irc') !!}
            </div>
        </div>
    </div>


    <div class="row">
        <p class="col-xs-12 col-md-8">
            Profile Picture - This is optional
        </p>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8">
            <div class="form-group {{ FlashNotification::hasErrorDetail('new_profile_photo', 'has-error has-feedback') }}">
                <label for="new_profile_photo" class="control-label">Profile Photo</label>
                <input type="file" name="new_profile_photo" id="new_profile_photo" class="form-control">
                {!! FlashNotification::getErrorDetail('new_profile_photo') !!}
                <span class="help-block">This photo will be displayed to members and may be used within the space, it will also be listed publicly on this site but you can turn that off below if you want.</span>
            </div>
            <div class="row">
            @if ($profileData->profile_photo)
            <div class="col-xs-6">
                <div class="form-group">
                    <strong>Existing Profile Image</strong><br />
                    <img src="{!! \BB\Helpers\UserImage::thumbnailUrl($user->hash) !!}" />
                </div>
            </div>
            @endif
            @if ($profileData->new_profile_photo)
            <div class="col-xs-6">
                <div class="form-group">
                    <strong>New Profile Image - pending review</strong><br />
                    <img src="{!! \BB\Helpers\UserImage::newThumbnailUrl($user->hash) !!}" />
                </div>
            </div>
            @endif
            </div>
        </div>
    </div>




    <div class="row">
        <div class="col-xs-12 col-md-8">
            <button type="submit" class="btn btn-primary">Save</button>
            <p></p>
        </div>
    </div>

</form>

</div>
@stop