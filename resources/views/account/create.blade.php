@extends('layouts.main')

@section('meta-title')
Join Hackspace Manchester
@stop

@section('content')

<div class="register-container col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">

    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h1>Join Hackspace Manchester</h1>
                <p>
                    Welcome! Hackspace Manchester is a fantastic space and community of like minded people.
                </p>
                <p>If you just want access to our online services such as the forum, please <a href="/online-only">sign up for online only access</a>.
            </div>
        </div>
    </div>

    {!! Form::open(array('route' => 'account.store', 'class'=>'form-horizontal', 'files'=>true)) !!}

    {!! Form::hidden('online_only', '0') !!}

    <div class="row">
        <div class="col-xs-12">
            <p>
                Please fill out the form below, on the next page you will be asked to setup a direct debit for the monthly payment.<br />
                <li>We need your real name and address, this is <a href="https://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general" target="_blank">required by UK law</a></li>
                <li>Your address will be kept private but your name will be listed publicly as being a member of our community</li>
            </p>
        </div>
    </div>

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
    <div class="form-group {{ Notification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">        
        {!! Form::label('announce_name', 'Announce Name', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('announce_name', null, ['class'=>'form-control', 'autocomplete'=>'announce-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('announce_name') !!}
        </div>
    </div>
    Note: announce name will be used to announce your entry into the Hackspace on our Hackscreen and Telegram Channel. <br>
    If you wish this to not be the case please set announce name to <i>anon</i>
    <br>
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

    <div class="form-group {{ Notification::hasErrorDetail('monthly_subscription', 'has-error has-feedback') }}">
        {!! Form::label('monthly_subscription', 'Monthly Subscription Amount', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            <div class="input-group">
                <div class="input-group-addon">&pound;</div>
                {!! Form::input('number', 'monthly_subscription', 20, ['class'=>'form-control', 'placeholder'=>'20', 'min'=>'12', 'step'=>'1']) !!}
            </div>
            {!! Notification::getErrorDetail('monthly_subscription') !!}
            <span class="help-block"><button type="button" class="btn btn-link" data-toggle="modal" data-target="#howMuchShouldIPayModal">How much should I pay?</button></span>
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
        {!! Form::label('address[line_1]', 'Address Line 1', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_1]', null, ['class'=>'form-control', 'autocomplete'=>'address-line1', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('address.line_1') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
        {!! Form::label('address[line_2]', 'Address Line 2', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_2]', null, ['class'=>'form-control', 'autocomplete'=>'address-line2']) !!}
            {!! Notification::getErrorDetail('address.line_2') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
        {!! Form::label('address[line_3]', 'Address Line 3', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_3]', null, ['class'=>'form-control', 'autocomplete'=>'address-level2']) !!}
            {!! Notification::getErrorDetail('address.line_3') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
        {!! Form::label('address[line_4]', 'Address Line 4', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_4]', null, ['class'=>'form-control', 'autocomplete'=>'address-level1']) !!}
            {!! Notification::getErrorDetail('address.line_4') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
        {!! Form::label('address[postcode]', 'Post Code', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[postcode]', null, ['class'=>'form-control', 'autocomplete'=>'postal-code', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('address.postcode') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('phone', 'has-error has-feedback') }}">
        {!! Form::label('phone', 'Phone', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::input('tel', 'phone', null, ['class'=>'form-control', 'autocomplete'=>'tel', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('phone') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
        {!! Form::label('emergency_contact', 'Emergency Contact', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('emergency_contact', null, ['class'=>'form-control', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('emergency_contact') !!}
            <span class="help-block">Please give us the name and contact details of someone we can contact if needed.</span>
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
            {!! Form::submit('Join Us', array('class'=>'btn btn-primary')) !!}
        </div>
    </div>


    {!! Form::close() !!}

</div>

<div class="modal fade" id="howMuchShouldIPayModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Subscription Suggestions</h4>
            </div>
            <div class="modal-body">
                <p>If you're not sure how much to pay, here are some general guidelines to help you find a suitable subscription amount for your circumstances:</p>

                &pound;12.50 - &pound;15 a month:
                <ul>
                    <li>You are on a low income and unable to afford a higher amount.</li>
                </ul>

                &pound;15 - &pound;20 a month:
                <ul>
                    <li>You are planning to visit the makerspace regularly and are a professional / in full-time employment</li>
                </ul>

                &pound;25 a month and up:
                <ul>
                    <li>You are planning to visit the makerspace regularly and would like to provide a little extra support (thank you!)</li>
                </ul>

                <p>
                    If you feel that the makerspace is worth more to you then please do adjust your subscription accordingly.
                    You can also change your subscription amount at any time!
                </p>

                <p>
                    If you would like to pay less than &pound;12.50 a month please select an amount over £12.50 and complete
                    this form, on the next page you will be asked to setup a subscription payment.
                    Before you do this please send the board an email letting them know how much you would like to
                    pay, they will then override the amount so you can continue to setup a subscription.
                </p>
            </div>
        </div>
    </div>
</div>

@stop
