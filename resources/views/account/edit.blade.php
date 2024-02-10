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
        {!! Form::model($user, array('route' => ['account.update', $user->id], 'method'=>'PUT', 'files'=>true)) !!}
        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="form-group {{ FlashNotification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
                    {!! Form::label('given_name', 'First Name') !!}
                    {!! Form::text('given_name', null, ['class'=>'form-control', 'autocomplete'=>'given-name']) !!}
                    {!! FlashNotification::getErrorDetail('given_name') !!}
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group {{ FlashNotification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
                    {!! Form::label('family_name', 'Family Name') !!}
                    {!! Form::text('family_name', null, ['class'=>'form-control', 'autocomplete'=>'family-name']) !!}
                    {!! FlashNotification::getErrorDetail('family_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
                    {!! Form::label('display_name', 'Username') !!}
                    {!! Form::text('display_name', null, ['class'=>'form-control', 'autocomplete'=>'off', 'readonly'=>'readonly']) !!}
                    <span class="help-block">Your Username will be used for display purposes on the members system, it cannot be changed once set without contacting the board </span>
                    {!! FlashNotification::getErrorDetail('display_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('pronouns', 'has-error has-feedback') }}">
                    {!! Form::label('pronouns', 'Pronouns (optional)') !!}
                    {!! Form::text('pronouns', null, ['class'=>'form-control']) !!}
                    {!! FlashNotification::getErrorDetail('pronouns') !!}
                    <span class="help-block">We want everybody to feel welcome at Hackspace Manchester. If you would like to share your pronouns on your profile, you can provide them here.</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">
                    {!! Form::label('announce_name', 'Entry Announcement Name (optional)') !!}
                    {!! Form::text('announce_name', null, ['class'=>'form-control', 'autocomplete'=>'off']) !!}
                    <span class="help-block">If you set a name here, each time you visit the Hackspace we will announce your arrival on a screen in the Hackspace, as well as the Hackscreen Telegram group.</span>
                    {!! FlashNotification::getErrorDetail('announce_name') !!}
                </div>
            </div>
        </div>

        <h3>Account information</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('email', 'has-error has-feedback') }}">
                    {!! Form::label('email', 'Email') !!}
                    {!! Form::text('email', null, ['class'=>'form-control', 'autocomplete'=>'email']) !!}
                    {!! FlashNotification::getErrorDetail('email') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
                    {!! Form::label('password', 'Password') !!}
                    {!! Form::password('password', ['class'=>'form-control', 'autocomplete'=>'off']) !!}
                    {!! FlashNotification::getErrorDetail('password') !!}
                </div>
            </div>
        </div>

        <h3>Contact Details</h3>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('secondary_email', 'has-error has-feedback') }}">
                    {!! Form::label('secondary_email', 'Alternate Email') !!}
                    {!! Form::text('secondary_email', null, ['class'=>'form-control', 'autocomplete'=>'off']) !!}
                    <span class="help-block"></span>
                    {!! FlashNotification::getErrorDetail('secondary_email') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('phone', 'has-error has-feedback') }}">
                    {!! Form::label('phone', 'Phone', ['class'=>'control-label']) !!}
                        {!! Form::input('tel', 'phone', $user->present()->phone, ['class'=>'form-control', 'x-autocompletetype'=>'tel']) !!}
                        {!! FlashNotification::getErrorDetail('phone') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
                    {!! Form::label('emergency_contact', 'Emergency Contact') !!}
                    {!! Form::text('emergency_contact', null, ['class'=>'form-control']) !!}
                    {!! FlashNotification::getErrorDetail('emergency_contact') !!}
                    <span class="help-block">Please give us the name and contact details of someone we can contact if needed</span>
                </div>
            </div>
        </div>

        <h3>Your address</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_1]', 'Address Line 1') !!}
                    {!! Form::text('address[line_1]', null, ['class'=>'form-control', 'autocomplete'=>'address-line-1']) !!}
                    {!! FlashNotification::getErrorDetail('address.line_1') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_2]', 'Address Line 2') !!}
                    {!! Form::text('address[line_2]', null, ['class'=>'form-control', 'autocomplete'=>'address-line-2']) !!}
                    {!! FlashNotification::getErrorDetail('address.line_2') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_3]', 'Address Line 3') !!}
                    {!! Form::text('address[line_3]', null, ['class'=>'form-control', 'autocomplete'=>'address-locality']) !!}
                    {!! FlashNotification::getErrorDetail('address.line_3') !!}
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_4]', 'Address Line 4') !!}
                    {!! Form::text('address[line_4]', null, ['class'=>'form-control', 'autocomplete'=>'region']) !!}
                    {!! FlashNotification::getErrorDetail('address.line_4') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
                    {!! Form::label('address[postcode]', 'Post Code') !!}
                    {!! Form::text('address[postcode]', null, ['class'=>'form-control', 'autocomplete'=>'postal-code']) !!}
                    {!! FlashNotification::getErrorDetail('address.postcode') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ FlashNotification::hasErrorDetail('profile_private', 'has-error has-feedback') }}">
                    {!! Form::checkbox('profile_private', true, null, ['class'=>'']) !!}
                    {!! Form::label('profile_private', 'Hide my Profile', ['class'=>'']) !!}
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
                            {!! Form::radio('newsletter', true, null, ['class'=>'']) !!}
                            Yes, I am happy to receive the membership newsletters
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            {!! Form::radio('newsletter', false, null, ['class'=>'']) !!}
                            No, please do not send me membership newsletters
                        </label>
                    </div>
                    {!! FlashNotification::getErrorDetail('newsletter') !!}
                </div>
            </div>
        </div>

        {!! Form::hidden('online_only', '0') !!}
        @if ($user->online_only)
            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group {{ FlashNotification::hasErrorDetail('online_only', 'has-error has-feedback') }}"
                        style="padding: 1em; background: white; border-left: 5px solid blue;" 
                    >
                    <h4>You're an online only user, and not a member of the space (yet).</h4>
                    <p>You can upgrade your account to a full member account if you want to join the space.</p>
                        {!! Form::checkbox('online_only', true, null, ['class'=>'']) !!}
                        {!! Form::label('online_only', 'Online only user', ['class'=>'']) !!}
                        {!! FlashNotification::getErrorDetail('online_only') !!}
                        <p>You'll need to fill in address fields and emergency contact information.</p>   
                        <p>Then uncheck this box in order to become a member of Hackspace Manchester. You'll need to set up payment information before your fob will work on the door.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-xs-12 col-md-8">
                {!! Form::submit('Update', array('class'=>'btn btn-primary')) !!}
                <p></p>
            </div>
        </div>

        {!! Form::close() !!}
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