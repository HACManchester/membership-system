@extends('layouts.main')

@section('meta-title')
Edit your details
@stop

@section('page-title')
Edit your details
@stop

@section('content')

<div class="panel panel-info">
    <div class="panel-heading">Your details, address, and preferences</div>
    <div class="panel-body">    
        <h3>Basic information</h3>                      
        <form action="{{ route('account.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="form-group {{ FlashNotification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
                    <label for="given_name">First Name</label>
                    <input type="text" name="given_name" id="given_name" class="form-control" autocomplete="given-name" value="{{ old('given_name', $user->given_name) }}">
                    {!! FlashNotification::getErrorDetail('given_name') !!}
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group {{ FlashNotification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
                    <label for="family_name">Family Name</label>
                    <input type="text" name="family_name" id="family_name" class="form-control" autocomplete="family-name" value="{{ old('family_name', $user->family_name) }}">
                    {!! FlashNotification::getErrorDetail('family_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
                    <label for="display_name">Username</label>
                    <input type="text" name="display_name" id="display_name" class="form-control" autocomplete="off" value="{{ old('display_name', $user->display_name) }}" {{ !Auth::user()->can('changeUsername', $user) ? 'readonly' : '' }}>
                    <span class="help-block">Your Username will be used for display purposes on the members system, it cannot be changed once set without contacting the board </span>
                    {!! FlashNotification::getErrorDetail('display_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('pronouns', 'has-error has-feedback') }}">
                    <label for="pronouns">Pronouns (optional)</label>
                    <input type="text" name="pronouns" id="pronouns" class="form-control" value="{{ old('pronouns', $user->pronouns) }}">
                    {!! FlashNotification::getErrorDetail('pronouns') !!}
                    <span class="help-block">We want everybody to feel welcome at Hackspace Manchester. If you would like to share your pronouns on your profile, you can provide them here.</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('suppress_real_name', 'has-error has-feedback') }}">
                    <label class="control-label">Real name privacy</label>
                    <div class="help-block">We understand some members are privacy conscious and may wish to keep their real name private from others in the community.</div>
                    <div>
                        <label>
                            <input type="radio" name="suppress_real_name" value="0" {{ old('suppress_real_name', $user->suppress_real_name) ? 'checked' : '' }}>
                            Yes, my real name may be shared with others
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="radio" name="suppress_real_name" value="1" {{ !old('suppress_real_name', $user->suppress_real_name) ? 'checked' : '' }}>
                            No, I'd like to keep my real name private
                        </label>
                    </div>
                    {!! FlashNotification::getErrorDetail('suppress_real_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">
                    <label for="announce_name">Entry Announcement Name (optional)</label>
                    <input type="text" name="announce_name" id="announce_name" class="form-control" autocomplete="off" value="{{ old('announce_name', $user->announce_name) }}">
                    <span class="help-block">If you set a name here, each time you visit the Hackspace we will announce your arrival on a screen in the Hackspace, as well as the Hackscreen Telegram group.</span>
                    {!! FlashNotification::getErrorDetail('announce_name') !!}
                </div>
            </div>
        </div>

        <h3>Account information</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('email', 'has-error has-feedback') }}">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" class="form-control" autocomplete="email" value="{{ old('email', $user->email) }}">
                    {!! FlashNotification::getErrorDetail('email') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" autocomplete="off">
                    {!! FlashNotification::getErrorDetail('password') !!}
                </div>
            </div>
        </div>

        <h3>Contact Details</h3>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('secondary_email', 'has-error has-feedback') }}">
                    <label for="secondary_email">Alternate Email</label>
                    <input type="text" name="secondary_email" id="secondary_email" class="form-control" autocomplete="off" value="{{ old('secondary_email', $user->secondary_email) }}">
                    <span class="help-block"></span>
                    {!! FlashNotification::getErrorDetail('secondary_email') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('phone', 'has-error has-feedback') }}">
                    <label for="phone" class="control-label">Phone</label>
                    <input type="tel" name="phone" id="phone" class="form-control" x-autocompletetype="tel" value="{{ old('phone', $user->present()->phone) }}">
                    {!! FlashNotification::getErrorDetail('phone') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
                    <label for="emergency_contact">Emergency Contact</label>
                    <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" value="{{ old('emergency_contact', $user->emergency_contact) }}">
                    {!! FlashNotification::getErrorDetail('emergency_contact') !!}
                    <span class="help-block">Please give us the name and contact details of someone we can contact if needed</span>
                </div>
            </div>
        </div>

        <h3>Your address</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
                    <label for="address_line_1">Address Line 1</label>
                    <input type="text" name="address[line_1]" id="address_line_1" class="form-control" autocomplete="address-line-1" value="{{ old('address.line_1', $user->address->line_1) }}">
                    {!! FlashNotification::getErrorDetail('address.line_1') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
                    <label for="address_line_2">Address Line 2</label>
                    <input type="text" name="address[line_2]" id="address_line_2" class="form-control" autocomplete="address-line-2" value="{{ old('address.line_2', $user->address->line_2) }}">
                    {!! FlashNotification::getErrorDetail('address.line_2') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
                    <label for="address_line_3">Address Line 3</label>
                    <input type="text" name="address[line_3]" id="address_line_3" class="form-control" autocomplete="address-locality" value="{{ old('address.line_3', $user->address->line_3) }}">
                    {!! FlashNotification::getErrorDetail('address.line_3') !!}
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
                    <label for="address_line_4">Address Line 4</label>
                    <input type="text" name="address[line_4]" id="address_line_4" class="form-control" autocomplete="region" value="{{ old('address.line_4', $user->address->line_4) }}">
                    {!! FlashNotification::getErrorDetail('address.line_4') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
                    <label for="address_postcode">Post Code</label>
                    <input type="text" name="address[postcode]" id="address_postcode" class="form-control" autocomplete="postal-code" value="{{ old('address.postcode', $user->address->postcode) }}">
                    {!! FlashNotification::getErrorDetail('address.postcode') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('profile_private', 'has-error has-feedback') }}">
                    <input type="checkbox" name="profile_private" id="profile_private" value="1" {{ old('profile_private', $user->profile_private) ? 'checked' : '' }}>
                    <label for="profile_private">Hide my Profile</label>
                    {!! FlashNotification::getErrorDetail('profile_private') !!}
                </div>
            </div>
        </div>

        <h3 id="newsletter">Newsletter</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div
                    class="form-group {{ FlashNotification::hasErrorDetail('newsletter', 'has-error has-feedback') }}"
                    role="radiogroup"
                    aria-labelledby="newsletter_label"
                >
                    <p>Newsletters will be sent out periodically to keep you up to date with announcements, news and events relevant to your membership of the space.</p>

                    <label id="newsletter_label">Do you want to receive the membership newsletters?</label>

                    <div class="radio">
                        <label>
                            <input type="radio" name="newsletter" value="1" {{ old('newsletter', $user->newsletter) ? 'checked' : '' }}>
                            Yes, I am happy to receive the membership newsletters
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="newsletter" value="0" {{ !old('newsletter', $user->newsletter) ? 'checked' : '' }}>
                            No, please do not send me membership newsletters
                        </label>
                    </div>
                    {!! FlashNotification::getErrorDetail('newsletter') !!}
                </div>
            </div>
        </div>

        <input type="hidden" name="online_only" value="0">
        @if ($user->online_only)
            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group {{ FlashNotification::hasErrorDetail('online_only', 'has-error has-feedback') }}"
                        style="padding: 1em; background: white; border-left: 5px solid blue;" 
                    >
                    <h4>You're an online only user, and not a member of the space (yet).</h4>
                    <p>You can upgrade your account to a full member account if you want to join the space.</p>
                        <input type="checkbox" name="online_only" id="online_only" value="1" {{ old('online_only', $user->online_only) ? 'checked' : '' }}>
                        <label for="online_only">Online only user</label>
                        {!! FlashNotification::getErrorDetail('online_only') !!}
                        <p>You'll need to fill in address fields and emergency contact information.</p>   
                        <p>Then uncheck this box in order to become a member of Hackspace Manchester. You'll need to set up payment information before your fob will work on the door.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <button type="submit" class="btn btn-primary">Update</button>
                <p></p>
            </div>
        </div>

        </form>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading">Access methods - key fobs and access codes</div>
        <div class="panel-body">
            <p>
                This section has moved to a new page:
                <a href="{{ route('keyfobs.index', $user->id) }}">Manage your access methods</a>
            </p>
        </div>
    </div>
</div>
    
@stop