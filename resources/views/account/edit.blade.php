@extends('layouts.main')

@section('meta-title')
Edit your details
@stop

@section('page-title')
Edit your details
@stop

@section('content')


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
        <div class="form-group {{ Notification::hasErrorDetail('email', 'has-error has-feedback') }}">
            {!! Form::label('email', 'Email') !!}
            {!! Form::text('email', null, ['class'=>'form-control', 'autocomplete'=>'email']) !!}
            {!! Notification::getErrorDetail('email') !!}
        </div>
    </div>
</div>
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
        <div class="form-group {{ Notification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">
            {!! Form::label('announce_name', 'Announce Name') !!}
            {!! Form::text('announce_name', null, ['class'=>'form-control', 'autocomplete'=>'off']) !!}
            <span class="help-block">Your Announce Name will be used for display purposes on some systems </span>
            {!! Notification::getErrorDetail('display_name') !!}
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


<div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="form-group {{ Notification::hasErrorDetail('profile_private', 'has-error has-feedback') }}">
            {!! Form::checkbox('profile_private', true, null, ['class'=>'']) !!}
            {!! Form::label('profile_private', 'Hide my Profile', ['class'=>'']) !!}
            {!! Notification::getErrorDetail('profile_private') !!}
        </div>
    </div>
</div>

<h4 id="newsletter">Newsletter Opt-In</h4>
<div class="row">
    <div class="col-xs-12 col-md-8">
        <div class="form-group {{ Notification::hasErrorDetail('newsletter', 'has-error has-feedback') }}">
            {!! Form::checkbox('newsletter', true, null, ['class'=>'']) !!}
            {!! Form::label('newsletter', 'Opt in to occasional newsletters', ['class'=>'']) !!}
            {!! Notification::getErrorDetail('newsletter') !!}
            <p>You can opt out at any time. Newsletters will be sent between once a month and once a quarter, and are for non-essential but interesting items such as goings on in the space, upcoming events etc.</p>
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

<h4 id="access">Key Fobs and Access Codes</h4>
@if (!$user->online_only)
    @if (!$user->keyfob())
        <div class="panel panel-info">
            <div class="panel-heading">Adding a keyfob</div>
            <div class="panel-body">
                <p><b>If you are at the space signup desk</b> select the text box to add a new fob, then scan your fob with the reader. Then hit "Add new fob"</p>   
            </div>
        </div>
    @endif

    <div class="panel panel-warning">
        <div class="panel-heading">Getting into the space</b></div>
        <div class="panel-body">
            <p>
            Active, paid up, non-banned members have two ways to access the space:
            <ul>
                <li>Using a fob - this is the primary method - enter the ID of the fob below to add a fob.</li>
                <li>Using an access code - once you have a fob, you may generate an access code which is auto-generated and cannot be edited.</li>
            </ul>
            Use is logged to prevent abuse of the space.
            </p>
        </div>
    </div>

    <h3>Your existing entry methods</h3>
    @if ($user->keyFobs()->count() == 0)
        <div class="panel panel-info">
            <div class="panel-heading">No entry methods</div>
            <div class="panel-body">
                <p>
                    You have no entry methods added to your account.
                </p>
            </div>
        </div>
    @else
        <ol>
        @foreach ($user->keyFobs()->get() as $fob)
            {!! Form::open(array('method'=>'DELETE', 'route' => ['keyfob.destroy', $fob->id], 'class'=>'form-horizontal')) !!}
                <li>
                    @if (substr( $fob->key_id, 0, 2 ) !== "ff")
                        <p>
                            <div class="badge" style="background:forestgreen">
                            🔑 Fob
                            </div>
                            <div class="badge">
                                Fob ID: {{ $fob->key_id }}
                            </div> 
                            <small>(added {{ $fob->created_at->toFormattedDateString() }})</small>
                        </p>
                        <div class="">
                            {!! Form::submit('Mark Fob Lost', array('class'=>'btn btn-default')) !!}
                        </div>
                    @else
                        <p>
                            <div class="badge" style="background:tomato">
                            🔢 Access Code
                            </div>
                            <div class="badge">
                                Code: {{ str_replace('f', '', $fob->key_id) }}
                            </div> 
                            <small>(added {{ $fob->created_at->toFormattedDateString() }})</small>
                        </p>
                        <div class="">
                            {!! Form::submit('Mark Code Lost', array('class'=>'btn btn-default')) !!}
                        </div>
                    @endif
                </li>
            {!! Form::hidden('user_id', $user->id) !!}
            {!! Form::close() !!}
        @endforeach
        </ol>
    @endif

    <h3>Add a new entry method</h3>
    @if ($user->induction_completed)
        @if ($user->keyFobs()->count() < 2)
            <div class="row well">
                <div class="col-md-6">
                    <h4>Add a Keyfob</h4>
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
                    {!! Form::hidden('user_id', $user->id) !!}
                    {!! Form::close() !!}
                </div>
                <div class="col-md-6">
                    <h4>Request access code</h4>
                    @if($user->keyFobs()->count() > 0)
                        {!! Form::open(array('method'=>'POST', 'route' => ['keyfob.store'], 'class'=>'form-horizontal')) !!}
                        <div class="form-group">
                            <div class="col-sm-3">
                                {!! Form::hidden('key_id', 'ff00000000') !!}
                                {!! Form::submit('Request access code', array('class'=>'btn btn-info')) !!}
                            </div>
                        </div>
                        {!! Form::hidden('user_id', $user->id) !!}
                        {!! Form::close() !!}
                    @else
                        <p><b>Sorry, you need to set up a fob first</b> This is so that new members meet with an existing member to set their fob up and get a general induction</p>
                    @endif
                </div>
            </div>
        @else
            <p>You have added the maximum number of entry methods permitted.</p>
        @endif
    @else
        <div class="panel panel-info">
            <div class="panel-heading">You need to do your online general induction</div>
            <div class="panel-body">
                <p>
                    You must <a href="/account/0/induction">complete your General Induction</a> before you can get physical access to the space.<br>
                    This is so you understand and agree to the rules.
                </p>
            </div>
        </div>
    @endif
@else
<div class="alert alert-danger">
    <b>Online User Only</b> You can't add access methods as you're an online only user. 
</div>
@endif
</div>

@stop