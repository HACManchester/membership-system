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

    {!! Form::open(array('route' => 'account.store', 'class'=>'form-horizontal', 'files'=>true)) !!}

    {!! Form::hidden('online_only', '1') !!}
    {!! Form::hidden('phone', '00000000000') !!}
    {!! Form::hidden('emergency_contact', '00000000000') !!}

    @if (FlashNotification::hasMessage())
    <div class="alert alert-{{ FlashNotification::getLevel() }} alert-dismissable">
        {!! FlashNotification::getMessage() !!}
    </div>
    @endif


    <div class="form-group {{ FlashNotification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
        {!! Form::label('given_name', 'First Name', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('given_name', null, ['class'=>'form-control', 'autocomplete'=>'given-name', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('given_name') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
        {!! Form::label('family_name', 'Surname', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('family_name', null, ['class'=>'form-control', 'autocomplete'=>'family-name', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('family_name') !!}
        </div>

    </div>
    <div class="form-group {{ FlashNotification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
        {!! Form::label('display_name', 'Username', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('display_name', null, ['class'=>'form-control', 'autocomplete'=>'display-name', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('display_name') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('pronouns', 'has-error has-feedback') }}">
        {!! Form::label('pronouns', 'Pronouns (optional)', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('pronouns', null, ['class'=>'form-control']) !!}
            {!! FlashNotification::getErrorDetail('pronouns') !!}
            <span class="help-block">We want everybody to feel welcome at Hackspace Manchester. If you would like to share your pronouns on your profile, you can provide them here.</span>
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('suppress_real_name', 'has-error has-feedback') }}">
        {!! Form::label(null, 'Real name privacy', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            <span class="help-block">We understand some members are privacy conscious and may wish to keep their real name private from others in the community.</span>
            <label>
                {!! Form::radio('suppress_real_name', '0', true) !!}
                Yes, my real name may be shared with others
            </label>
            <label>
                {!! Form::radio('suppress_real_name', '1',  false) !!}
                No, I'd like to keep my real name private
            </label>
            {!! FlashNotification::getErrorDetail('suppress_real_name') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('email', 'has-error has-feedback') }}">
        {!! Form::label('email', 'Email', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::input('email', 'email', null, ['class'=>'form-control', 'autocomplete'=>'email', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('email') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
        {!! Form::label('password', 'Password', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::password('password', ['class'=>'form-control', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('password') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
        <div class="col-sm-9 col-lg-7 col-sm-offset-3">
            <span class="help-block">Please read the <a href="https://hacman.org.uk/rules" target="_blank">rules</a> and click the checkbox to confirm you agree to them</span>
            {!! Form::checkbox('rules_agreed', true, null, ['class'=>'']) !!}
            {!! Form::label('rules_agreed', 'I agree to the Hackspace Manchester rules', ['class'=>'']) !!}
            {!! FlashNotification::getErrorDetail('rules_agreed') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            {!! Form::submit('Get Online Access', array('class'=>'btn btn-primary')) !!}
        </div>
    </div>


    {!! Form::close() !!}

</div>
@stop
