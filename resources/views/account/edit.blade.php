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

<div class="row">
    <div class="col-xs-12 col-md-8">
        {!! Form::submit('Update', array('class'=>'btn btn-primary')) !!}
        <p></p>
    </div>
</div>

{!! Form::close() !!}

        <h4>Key Fob</h4>
        <p>Scan your fob with the reader to enter your fob ID and click add new fob</p>
        @foreach ($user->keyFobs()->get() as $fob)
        {!! Form::open(array('method'=>'DELETE', 'route' => ['keyfob.destroy', $fob->id], 'class'=>'form-horizontal')) !!}
            <div class="form-group">
                <div class="col-sm-5">
                    <p class="form-control-static">{{ $fob->key_id }} <small>(added {{ $fob->created_at->toFormattedDateString() }})</small></p>
                </div>
                <div class="col-sm-3">
                    {!! Form::submit('Mark Lost', array('class'=>'btn btn-default')) !!}
                </div>
            </div>
        {!! Form::hidden('user_id', $user->id) !!}
        {!! Form::close() !!}
        @endforeach

        @if ($user->keyFobs()->count() < 2)
            {!! Form::open(array('method'=>'POST', 'route' => ['keyfob.store'], 'class'=>'form-horizontal')) !!}
            <div class="form-group">
                <div class="col-sm-5">
                    {!! Form::text('key_id', '', ['class'=>'form-control']) !!}
                </div>
                <div class="col-sm-3">
                    {!! Form::submit('Add a new fob', array('class'=>'btn btn-default')) !!}
                </div>
            </div>
            {!! Form::hidden('user_id', $user->id) !!}
            {!! Form::close() !!}
        @endif

    </div>

@stop