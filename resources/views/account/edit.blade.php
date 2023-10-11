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
                <div class="form-group {{ Notification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
                    {!! Form::label('given_name', 'First Name') !!}
                    {!! Form::text('given_name', null, ['class'=>'form-control', 'autocomplete'=>'given-name']) !!}
                    {!! Notification::getErrorDetail('given_name') !!}
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group {{ Notification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
                    {!! Form::label('family_name', 'Family Name') !!}
                    {!! Form::text('family_name', null, ['class'=>'form-control', 'autocomplete'=>'family-name']) !!}
                    {!! Notification::getErrorDetail('family_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
                    {!! Form::label('display_name', 'Username') !!}
                    {!! Form::text('display_name', null, ['class'=>'form-control', 'autocomplete'=>'off', 'readonly'=>'readonly']) !!}
                    <span class="help-block">Your Username will be used for display purposes on the members system, it cannot be changed once set without contacting the board </span>
                    {!! Notification::getErrorDetail('display_name') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('pronouns', 'has-error has-feedback') }}">
                    {!! Form::label('pronouns', 'Pronouns (optional)') !!}
                    {!! Form::text('pronouns', null, ['class'=>'form-control']) !!}
                    {!! Notification::getErrorDetail('pronouns') !!}
                    <span class="help-block">We want everybody to feel welcome at Hackspace Manchester. If you would like to share your pronouns on your profile, you can provide them here.</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">
                    {!! Form::label('announce_name', 'Entry Announcement Name (optional)') !!}
                    {!! Form::text('announce_name', null, ['class'=>'form-control', 'autocomplete'=>'off']) !!}
                    <span class="help-block">If you set a name here, each time you visit the Hackspace we will announce your arrival on a screen in the Hackspace, as well as the Hackscreen Telegram group.</span>
                    {!! Notification::getErrorDetail('announce_name') !!}
                </div>
            </div>
        </div>

        <h3>Account information</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('email', 'has-error has-feedback') }}">
                    {!! Form::label('email', 'Email') !!}
                    {!! Form::text('email', null, ['class'=>'form-control', 'autocomplete'=>'email']) !!}
                    {!! Notification::getErrorDetail('email') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('password', 'has-error has-feedback') }}">
                    {!! Form::label('password', 'Password') !!}
                    {!! Form::password('password', ['class'=>'form-control', 'autocomplete'=>'off']) !!}
                    {!! Notification::getErrorDetail('password') !!}
                </div>
            </div>
        </div>

        <h3>Contact Details</h3>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('secondary_email', 'has-error has-feedback') }}">
                    {!! Form::label('secondary_email', 'Alternate Email') !!}
                    {!! Form::text('secondary_email', null, ['class'=>'form-control', 'autocomplete'=>'off']) !!}
                    <span class="help-block"></span>
                    {!! Notification::getErrorDetail('secondary_email') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('phone', 'has-error has-feedback') }}">
                    {!! Form::label('phone', 'Phone', ['class'=>'control-label']) !!}
                        {!! Form::input('tel', 'phone', $user->present()->phone, ['class'=>'form-control', 'x-autocompletetype'=>'tel']) !!}
                        {!! Notification::getErrorDetail('phone') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
                    {!! Form::label('emergency_contact', 'Emergency Contact') !!}
                    {!! Form::text('emergency_contact', null, ['class'=>'form-control']) !!}
                    {!! Notification::getErrorDetail('emergency_contact') !!}
                    <span class="help-block">Please give us the name and contact details of someone we can contact if needed</span>
                </div>
            </div>
        </div>

        <h3>Your address</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_1]', 'Address Line 1') !!}
                    {!! Form::text('address[line_1]', null, ['class'=>'form-control', 'autocomplete'=>'address-line-1']) !!}
                    {!! Notification::getErrorDetail('address.line_1') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_2]', 'Address Line 2') !!}
                    {!! Form::text('address[line_2]', null, ['class'=>'form-control', 'autocomplete'=>'address-line-2']) !!}
                    {!! Notification::getErrorDetail('address.line_2') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_3]', 'Address Line 3') !!}
                    {!! Form::text('address[line_3]', null, ['class'=>'form-control', 'autocomplete'=>'address-locality']) !!}
                    {!! Notification::getErrorDetail('address.line_3') !!}
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
                    {!! Form::label('address[line_4]', 'Address Line 4') !!}
                    {!! Form::text('address[line_4]', null, ['class'=>'form-control', 'autocomplete'=>'region']) !!}
                    {!! Notification::getErrorDetail('address.line_4') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
                    {!! Form::label('address[postcode]', 'Post Code') !!}
                    {!! Form::text('address[postcode]', null, ['class'=>'form-control', 'autocomplete'=>'postal-code']) !!}
                    {!! Notification::getErrorDetail('address.postcode') !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div class="form-group {{ Notification::hasErrorDetail('profile_private', 'has-error has-feedback') }}">
                    {!! Form::checkbox('profile_private', true, null, ['class'=>'']) !!}
                    {!! Form::label('profile_private', 'Hide my Profile', ['class'=>'']) !!}
                    {!! Notification::getErrorDetail('profile_private') !!}
                </div>
            </div>
        </div>

        <h3 id="newsletter">Newsletter</h3>
        <div class="row">
            <div class="col-xs-12 col-md-8">
                <div
                    class="form-group {{ Notification::hasErrorDetail('newsletter', 'has-error has-feedback') }}"
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
                    {!! Notification::getErrorDetail('newsletter') !!}
                </div>
            </div>
        </div>

        {!! Form::hidden('online_only', '0') !!}
        @if ($user->online_only)
            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group {{ Notification::hasErrorDetail('online_only', 'has-error has-feedback') }}"
                        style="padding: 1em; background: white; border-left: 5px solid blue;" 
                    >
                    <h4>You're an online only user, and not a member of the space (yet).</h4>
                    <p>You can upgrade your account to a full member account if you want to join the space.</p>
                        {!! Form::checkbox('online_only', true, null, ['class'=>'']) !!}
                        {!! Form::label('online_only', 'Online only user', ['class'=>'']) !!}
                        {!! Notification::getErrorDetail('online_only') !!}
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
        @if (!$user->online_only)
            @if($user->isAdmin() || $user->induction_completed || $user->keyFobs()->count() > 0)
               
                <div class="row">
                    <div class="col-md-6">
                        <h4>How 24/7 access works</h4>
                        <p>
                            Active, paid up, non-banned members have two ways to access the space:
                            <ul>
                                <li>Using a fob - this is the primary method - enter the ID of the fob below to add a fob.</li>
                                <li>Using an access code - once you have a fob, you may generate an access code which is auto-generated and cannot be edited.</li>
                            </ul>
                            Entries to the space are securely logged to prevent abuse of the space.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h4>Your access to the space</h4>
                        @if ($user->keyFobs()->count() == 0)
                            <div class="alert alert-warning">
                                <strong>You have no entry methods</strong> and won't be able to access the space outside of open evenings.
                            </div>
                        @else
                            <!--
                            <p>
                                @if ($user->announce_name)
                                    🎉 Your announce name is set to: <code>{{$user->announce_name}}</code> (<a href="#announce_name">edit</a>)
                                @else
                                    🗣️ You don't have an announce name set, why not <a href="#announce_name">make an entrance</a> and set an announce name? (optional) 
                                @endif
                                <br><br>
                                Announce names are announced in the space and on the Hackscreen chat when you enter - have fun, set a conversation starter, just don't add anything rude, offensive or personal. 
                            </p>
                            -->
                
                            @if ($user->keyFobs()->count() > 0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <ol class="list-group">
                                            @foreach ($user->keyFobs()->get() as $fob)
                                                {!! Form::open(array('method'=>'DELETE', 'route' => ['keyfob.destroy', $fob->id], 'class'=>'form-horizontal')) !!}
                                                    <li class="list-group-item row">
                                                        <div class="col-md-6">
                                                            @if (substr( $fob->key_id, 0, 2 ) !== "ff")
                                                                <h4>
                                                                    <span class="label label-info"  style="background:forestgreen">
                                                                        🔑 Fob
                                                                    </span>
                                                                </h4>
                                                                <h4>Fob ID:{{ $fob->key_id }}</h4> 
                                                            @else
                                                                <h4>
                                                                    <span class="label label-info" style="background:tomato">
                                                                        🔢 Access Code
                                                                    </span>
                                                                </h4>
                                                                <h4>
                                                                    Code: {{ str_replace('f', '', $fob->key_id) }}
                                                                </h4>
                                                            @endif
                                                            
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class=" pull-right">
                                                                @if (substr( $fob->key_id, 0, 2 ) !== "ff")   
                                                                    <small>(added {{ $fob->created_at->toFormattedDateString() }})</small>
                                                                    {!! Form::submit('Mark Fob Lost', array('class'=>'btn btn-default')) !!}
                                                                @else
                                                                    <small>(added {{ $fob->created_at->toFormattedDateString() }})</small>
                                                                    {!! Form::submit('Mark Code Lost', array('class'=>'btn btn-default')) !!}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </li>
                                                {!! Form::hidden('user_id', $user->id) !!}
                                                {!! Form::close() !!}
                                            @endforeach
                                        </ol>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                

                <h3>Add a new entry method</h3>
                @if (($user->keyFobs()->count() < 2 && $user->induction_completed) || $user->isAdmin())
                <p>
                    You may add a fob or generate an access code (you'll be assigned a random 8 digit number). Present the fob or access code to the keypad to get into the space. 
                    <br>Please leave five minutes after updating your details for them to work on the door.
                </p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Add a keyfob</h4>
                            <p><strong>In the hackspace?</strong> Select a fob from the pot, select the text box below, then scan your fob with the reader. The ID will be typed in.</p>
                            
                            {!! Form::open(array('method'=>'POST', 'route' => ['keyfob.store'], 'class'=>'form-horizontal')) !!}
                            <div class="form-group">
                                <div class="col-sm-5">
                                    {!! Form::text('key_id', '', ['class'=>'form-control']) !!}
                                    Characters A-F and numbers 0-9 only.
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::submit('Add a new fob', array('class'=>'btn btn-primary')) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                        <div class="col-md-6">
                            <h4>Request access code</h4>
                            <p>You'll be assigned a random 8 digit access code.</p>
                            {!! Form::open(array('method'=>'POST', 'route' => ['keyfob.store'], 'class'=>'form-horizontal')) !!}
                            <div class="form-group">
                                <div class="col-sm-3">
                                    {!! Form::hidden('key_id', 'ff00000000') !!}
                                    {!! Form::submit('Request access code', array('class'=>'btn btn-info')) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                            
                        </div>
                    </div>  
                
                @else
                    <p>You have added the maximum number of entry methods permitted.</p>
                @endif
            @else
                <div class="alert alert-warning">
                    You need to have been given the general induction before you can add access methods. 
                </div>
            @endif
        @else
            <div class="alert alert-danger">
                <b>Online User Only</b> You can't add access methods as you're an online only user. 
            </div>
        @endif
    </div>
    </div>
    </div>
    
@stop