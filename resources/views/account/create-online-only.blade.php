@extends('layouts.main')

@section('meta-title')
Join Hackspace Manchester
@stop

@section('content')

<div class="register-container col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">

    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h1>Hackspace Manchester - <b>Online Only Access</b></h1>
                <p>
                    Hackspace Manchester is a fantastic space and community of like minded people.<br/>
                    Filling out this form gives you online access only - you won't be able to visit the physical space and you won't be a member.<br/>
                    If you'd like to become a member, you need to <a href="/register">register</a>.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('account.store') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="online_only" value="1">
        <input type="hidden" name="phone" value="00000000000">
        <input type="hidden" name="emergency_contact" value="00000000000">

        @if (FlashNotification::hasMessage())
        <div class="alert alert-{{ FlashNotification::getLevel() }} alert-dismissable">
            {!! FlashNotification::getMessage() !!}
        </div>
        @endif

        <div class="form-group {{ FlashNotification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
            <label for="given_name" class="col-sm-3 control-label">First Name</label>
            <div class="col-sm-9 col-lg-7">
                <input type="text" name="given_name" id="given_name" class="form-control" autocomplete="given-name" required value="{{ old('given_name') }}">
                {!! FlashNotification::getErrorDetail('given_name') !!}
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
            <label for="family_name" class="col-sm-3 control-label">Surname</label>
            <div class="col-sm-9 col-lg-7">
                <input type="text" name="family_name" id="family_name" class="form-control" autocomplete="family-name" required value="{{ old('family_name') }}">
                {!! FlashNotification::getErrorDetail('family_name') !!}
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
            <label for="display_name" class="col-sm-3 control-label">Username</label>
            <div class="col-sm-9 col-lg-7">
                <input type="text" name="display_name" id="display_name" class="form-control" autocomplete="display-name" required value="{{ old('display_name') }}">
                {!! FlashNotification::getErrorDetail('display_name') !!}
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('pronouns', 'has-error has-feedback') }}">
            <label for="pronouns" class="col-sm-3 control-label">Pronouns (optional)</label>
            <div class="col-sm-9 col-lg-7">
                <input type="text" name="pronouns" id="pronouns" class="form-control" value="{{ old('pronouns') }}">
                {!! FlashNotification::getErrorDetail('pronouns') !!}
                <span class="help-block">We want everybody to feel welcome at Hackspace Manchester. If you would like to share your pronouns on your profile, you can provide them here.</span>
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('suppress_real_name', 'has-error has-feedback') }}">
            <label class="col-sm-3 control-label">Real name privacy</label>
            <div class="col-sm-9 col-lg-7">
                <span class="help-block">We understand some members are privacy conscious and may wish to keep their real name private from others in the community.</span>
                <label>
                    <input type="radio" name="suppress_real_name" value="0" {{ old('suppress_real_name', '0') === '0' ? 'checked' : '' }}>
                    Yes, my real name may be shared with others
                </label>
                <label>
                    <input type="radio" name="suppress_real_name" value="1" {{ old('suppress_real_name') === '1' ? 'checked' : '' }}>
                    No, I'd like to keep my real name private
                </label>
                {!! FlashNotification::getErrorDetail('suppress_real_name') !!}
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('email', 'has-error has-feedback') }}">
            <label for="email" class="col-sm-3 control-label">Email</label>
            <div class="col-sm-9 col-lg-7">
                <input type="email" name="email" id="email" class="form-control" autocomplete="email" required value="{{ old('email') }}">
                {!! FlashNotification::getErrorDetail('email') !!}
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
            <label for="password" class="col-sm-3 control-label">Password</label>
            <div class="col-sm-9 col-lg-7">
                <input type="password" name="password" id="password" class="form-control" required>
                {!! FlashNotification::getErrorDetail('password') !!}
            </div>
        </div>

        <div class="form-group {{ FlashNotification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
            <div class="col-sm-9 col-lg-7 col-sm-offset-3">
                <span class="help-block">Please read the <a href="https://hacman.org.uk/rules" target="_blank">rules</a> and click the checkbox to confirm you agree to them</span>
                <input type="checkbox" name="rules_agreed" id="rules_agreed" value="1" {{ old('rules_agreed') ? 'checked' : '' }}>
                <label for="rules_agreed">I agree to the Hackspace Manchester rules</label>
                {!! FlashNotification::getErrorDetail('rules_agreed') !!}
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <button type="submit" class="btn btn-primary">Get Online Access</button>
            </div>
        </div>
    </form>

</div>
@stop
