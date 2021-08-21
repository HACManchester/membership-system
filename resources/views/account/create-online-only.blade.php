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

    @if (Notification::hasMessage())
    <div class="alert alert-{{ Notification::getLevel() }} alert-dismissable">
        {!! Notification::getMessage() !!}
    </div>
    @endif


    <div class="form-group {{ Notification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
        {!! Form::label('given_name', 'First Name', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('given_name', null, ['class'=>'form-control', 'autocomplete'=>'given-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('given_name') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
        {!! Form::label('family_name', 'Surname', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('family_name', null, ['class'=>'form-control', 'autocomplete'=>'family-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('family_name') !!}
        </div>

    </div>
    <div class="form-group {{ Notification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
        {!! Form::label('display_name', 'Username', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('display_name', null, ['class'=>'form-control', 'autocomplete'=>'display-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('display_name') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('email', 'has-error has-feedback') }}">
        {!! Form::label('email', 'Email', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::input('email', 'email', null, ['class'=>'form-control', 'autocomplete'=>'email', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('email') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('password', 'has-error has-feedback') }}">
        {!! Form::label('password', 'Password', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::password('password', ['class'=>'form-control', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('password') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
        <div class="col-sm-9 col-lg-7 col-sm-offset-3">
            <span class="help-block">Please read the <a href="https://members.hacman.org.uk/resources/policy/rules" target="_blank">rules</a> and click the checkbox to confirm you agree to them</span>
            {!! Form::checkbox('rules_agreed', true, null, ['class'=>'']) !!}
            {!! Form::label('rules_agreed', 'I agree to the Hackspace Manchester rules', ['class'=>'']) !!}
            {!! Notification::getErrorDetail('rules_agreed') !!}
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
