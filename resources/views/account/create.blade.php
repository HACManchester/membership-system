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
            </div>
        </div>
    </div>

    @if($gift)
        <div class="row">
            <div class="col-xs-12">
                <div class="alert {!! $gift_valid ? 'alert-success' : 'alert-danger'!!}">
                    @if($gift_valid)
                        <h3>🎁 Gift Code Added!</h3>
                        <p>
                            Hey {!! $gift_details['to'] !!}, your gift from {!! $gift_details['from'] !!} has been applied!
                            Just register below and you'll enjoy 
                            @if($gift_details['months']) 
                                <b>{!! $gift_details['months'] !!} months</b> of membership for free 
                            @endif

                            @if($gift_details['months'] && $gift_details['credit'])
                                and
                            @endif

                            @if($gift_details['credit']) 
                                <b>£{!! $gift_details['credit'] !!} credit</b>!
                            @endif
                        </p>
                    @else
                        <h3>😔 We couldn't find that gift code...</h3>
                        <p>
                            Hmmm, that code wasn't valid.<br/>
                            You can <a href="/gift">try again</a> or register below without the gift.
                        </p>
                    @endif
            </div>
            </div>
        </div>  
    @endif

    {!! Form::open(array('route' => 'account.store', 'class'=>'form-horizontal', 'files'=>true)) !!}

    {!! Form::hidden('online_only', '0') !!}

    @if($gift)
        {!! Form::hidden('gift_code', $gift_code) !!}
    @endif

    <div class="row">
        <div class="col-xs-12">
            <p>
                Please fill out the form below, on the next page you will be asked to setup a direct debit for the monthly payment.<br />
                <ul>
                    <li>We need your real name and address, this is <a href="https://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general" target="_blank">required by UK law</a></li>
                    <li>Your address will be kept private but your name will be listed publicly as being a member of our community</li>
                </ul>
            </p>
        </div>
    </div>

    @if (FlashNotification::hasMessage())
    <div class="alert alert-{{ FlashNotification::getLevel() }} alert-dismissable">
        {!! FlashNotification::getMessage() !!}
    </div>
    @endif

    <h4>Basic Informaton</h4>
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

    <!--
    <div class="form-group {{ FlashNotification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">        
        {!! Form::label('announce_name', 'Announce Name', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('announce_name', null, ['class'=>'form-control', 'autocomplete'=>'announce-name']) !!}
            {!! FlashNotification::getErrorDetail('announce_name') !!}
        </div>
    </div>
    Make an entrance! Announce name, if set, will be used to announce your entry into the Hackspace both in the space and on the Hackscreen Telegram Channel.<br>
    <br>
    -->

    <h4>Account information</h4>

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

    @if($gift_valid)
    <div>
        <div class="alert alert-success">
            As you have a gift certificate applied, <b>you won't pay for the duration of your free membership</b>. 
        </div>
    </div>
    @endif

    <div class="alert {!! $gift_valid ? 'alert-info' : 'alert-success' !!}">
        <div class="form-group {{ FlashNotification::hasErrorDetail('monthly_subscription', 'has-error has-feedback') }}">
            {!! Form::label('monthly_subscription', 'Monthly Subscription Amount', ['class'=>'col-sm-3 control-label']) !!}
            <div class="col-sm-9 col-lg-7">
                <div class="input-group">
                    <div class="input-group-addon">&pound;</div>
                    {!! Form::input('number', 'monthly_subscription', $recommendedAmount / 100, ['class' => 'form-control', 'placeholder' => $recommendedAmount / 100, 'min' => $minAmount / 100, 'step' => '1']) !!}
                </div>
                {!! FlashNotification::getErrorDetail('monthly_subscription') !!}
                <span class="help-block"><button type="button" class="btn btn-link" data-toggle="modal" data-target="#howMuchShouldIPayModal">How much should I pay?</button></span>
            </div>
        </div>
        @if($gift_valid)
        <ul>
            <li>
                At any time, you can set up payment details which will mean your membership will roll on after the free duration, at the amount you choose here. 
            </li>
            <li>
                You can change this amount at any time - so if you're not sure you can leave it.
            </li>
            <li>
                If you don't add payment details, your membership will automatically expire after your free gift period.
            </li>
        </ul>
        @else
        <ul>
            <li>{{ MembershipPayments::formatPrice($recommendedAmount) }} a month, like a cheap gym membership, is the recommended amount for new starters.</li>
            <li>You can pay less, perhaps if you're on lower income, but we do ask for a minimum of {{ MembershipPayments::formatPrice($minAmount) }}. Click the link above for more advice.</li>
            <li>You can pay more (&pound;25-&pound;50) if you'd like to support the space.</li>
        </ul>
        @endif
    </div>

    <h4>Contact Details</h4>
    <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
        {!! Form::label('address[line_1]', 'Address Line 1', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_1]', null, ['class'=>'form-control', 'autocomplete'=>'address-line1', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('address.line_1') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
        {!! Form::label('address[line_2]', 'Address Line 2', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_2]', null, ['class'=>'form-control', 'autocomplete'=>'address-line2']) !!}
            {!! FlashNotification::getErrorDetail('address.line_2') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
        {!! Form::label('address[line_3]', 'Address Line 3', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_3]', null, ['class'=>'form-control', 'autocomplete'=>'address-level2']) !!}
            {!! FlashNotification::getErrorDetail('address.line_3') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
        {!! Form::label('address[line_4]', 'Address Line 4', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_4]', null, ['class'=>'form-control', 'autocomplete'=>'address-level1']) !!}
            {!! FlashNotification::getErrorDetail('address.line_4') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
        {!! Form::label('address[postcode]', 'Post Code', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[postcode]', null, ['class'=>'form-control', 'autocomplete'=>'postal-code', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('address.postcode') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('phone', 'has-error has-feedback') }}">
        {!! Form::label('phone', 'Phone', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::input('tel', 'phone', null, ['class'=>'form-control', 'autocomplete'=>'tel', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('phone') !!}
        </div>
    </div>

    <div class="form-group {{ FlashNotification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
        {!! Form::label('emergency_contact', 'Emergency Contact', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('emergency_contact', null, ['class'=>'form-control', 'required' => 'required']) !!}
            {!! FlashNotification::getErrorDetail('emergency_contact') !!}
            <span class="help-block">Please give us the name and contact details of someone we can contact if needed.</span>
        </div>
    </div>      

    <div class="form-group {{ FlashNotification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
        <div class="col-xs-10 col-sm-8 well col-lg-8 col-xs-offset-1 col-sm-offset-3" style="background:rgba(255,0,0,0.05)">
            <h4>Rules</h4>
            <span class="help-block">Please read the <a href="https://hacman.org.uk/rules" target="_blank">rules</a> and click the checkbox to confirm you agree to them</span>
            {!! Form::checkbox('rules_agreed', true, null, ['class'=>'']) !!}
            {!! Form::label('rules_agreed', 'I agree to the Hackspace Manchester rules', ['class'=>'']) !!}
            {!! FlashNotification::getErrorDetail('rules_agreed') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            {!! Form::submit('Join Hackspace Manchester', array('class'=>'btn btn-primary')) !!}
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

                Minimum {{ MembershipPayments::formatPrice($minAmount) }} a month:
                <ul>
                    <li>You are on a low income and unable to afford a higher amount.</li>
                </ul>

                {{ MembershipPayments::formatPrice($recommendedAmount) }} a month:
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
                    If you would like to pay less than {{ MembershipPayments::formatPrice($minAmount) }} a month please select an amount over {{ MembershipPayments::formatPrice($minAmount) }} and complete
                    this form, on the next page you will be asked to setup a subscription payment.
                    Before you do this please send the board an email letting them know how much you would like to
                    pay, they will then override the amount so you can continue to setup a subscription.
                </p>
            </div>
        </div>
    </div>
</div>