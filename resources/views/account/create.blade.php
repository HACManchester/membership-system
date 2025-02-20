@extends('layouts.main')

@section('meta-title')
Join Hackspace Manchester
@endsection

@section('content')
<div class="register-container col-xs-12 col-lg-10 col-lg-offset-1">

    <div class="page-header">
        <h1>Join Hackspace Manchester</h1>
        <p>
            Welcome! Hackspace Manchester is a fantastic space and community of like minded people.
        </p>
    </div>

    @if($gift)
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
    @endif

    {!! Form::open(array('route' => 'account.store', 'class'=>'form-horizontal', 'files'=>true)) !!}

    {!! Form::hidden('online_only', '0') !!}

    @if($gift)
        {!! Form::hidden('gift_code', $gift_code) !!}
    @endif

    <p>
        Please fill out the form below, on the next page you will be asked to setup a direct debit for the monthly payment.<br />
        <ul>
            <li>We need your real name and address, this is <a href="https://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general" target="_blank">required by UK law</a></li>
            <li>Your address will be kept private</li>
            <li>Your username will be visible to other members of our community</li>
            <li>You may choose whether you'd like to share or hide your real name with others in our community</li>
        </ul>
    </p>

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

    <div class="form-group {{ FlashNotification::hasErrorDetail('suppress_real_name', 'has-error has-feedback') }}">
        {!! Form::label(null, 'Real name privacy', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            <p class="help-block">We understand some members are privacy conscious and may wish to keep their real name private from others in the community.</p>
            <div>
                <label>
                    {!! Form::radio('suppress_real_name', '0', true) !!}
                    Yes, my real name may be shared with others
                </label>
            </div>
            <div>
                <label>
                    {!! Form::radio('suppress_real_name', '1',  false) !!}
                    No, I'd like to keep my real name private
                </label>
            </div>
            {!! FlashNotification::getErrorDetail('suppress_real_name') !!}
        </div>
    </div>

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
        <div class="alert alert-success">
            As you have a gift certificate applied, <b>you won't pay for the duration of your free membership</b>. 
        </div>
    @endif


    <h4>Membership payment</h4>

    <div class="form-group {{ FlashNotification::hasErrorDetail('monthly_subscription', 'has-error has-feedback') }}">
        {!! FlashNotification::getErrorDetail('membership_tier') !!}
        <div>
            @foreach($priceOptions as $option)
                <div class="col-sm-3">
                    <div class="panel panel-default" onclick="document.getElementById('subscription_{{ $option->value_in_pence }}').click()">
                        <div class="panel-heading">
                            {!! Form::radio('membership_tier', $option->value_in_pence / 100, $option->value_in_pence == $recommendedAmount, ['class' => 'form-check-input', 'id' => 'subscription_' . $option->value_in_pence]) !!}
                            
                            {!! Form::label('subscription_' . $option->value_in_pence, $option->title . ': £' . number_format($option->value_in_pence / 100, 2), ['class' => 'form-check-label']) !!}
                        </div>
                        <div class="panel-body">
                            <p>{!! nl2br($option->description) !!}</p>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-sm-3">
                <div class="panel panel-default" onclick="document.getElementById('subscription_custom').click()">
                    <div class="panel-heading">
                        {!! Form::radio('membership_tier', 'custom', false, ['class' => 'form-check-input', 'id' => 'subscription_custom']) !!}
                        {!! Form::label('subscription_custom', 'Custom Amount', ['class' => 'form-check-label']) !!}
                    </div>
                    <div class="panel-body">
                        <p>For those that want to go above and beyond to support the makerspace, enter a custom amount here:</p>
                        {!! Form::label('custom_subscription_amount', 'Custom Amount', ['class' => 'form-check-label']) !!}
                        {!! Form::input('number', 'monthly_subscription', $recommendedAmount / 100, ['class' => 'form-control', 'placeholder' => 'Enter amount', 'min' => $minAmount / 100, 'step' => '1']) !!}
                        {!! FlashNotification::getErrorDetail('monthly_subscription') !!}
                    </div>
                </div>
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

    <div class="alert alert-warning">
        <h4>Pacemaker warning</h4>

        <p>
            Some of our tools may pose a risk to those that have pacemakers fitted. This risk may be especially high around
            the welding equipment. Please consult your doctor about the risks of using machinery when wearing a pacemaker.
            If you do undergo an induction on any tools that carry a risk, please mention this to your trainer.
        </p>
    </div>

    <h4>Rules</h4>
    <p>
        We want Hackspace Manchester to be a welcoming and inclusive environment, where everybody feels comfortable and
        behaves safely. Please familiarise yourself with
        <a href="https://hacman.org.uk/rules" target="_blank">our rules and code of conduct</a>.
    </p>
    <div class="form-group {{ FlashNotification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
        <div class="col-xs-12">
            <div class="checkbox">
                <label>
                    {!! Form::checkbox('rules_agreed', true, null, ['class'=>'']) !!}
                    I agree to the Hackspace Manchester rules and code of conduct
                </label>
                {!! FlashNotification::getErrorDetail('rules_agreed') !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-xs-12">
            {!! Form::submit('Join Hackspace Manchester', array('class'=>'btn btn-primary')) !!}
        </div>
    </div>


    {!! Form::close() !!}

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const membershipTiers = document.querySelectorAll('input[name="membership_tier"]');
        const customAmountInput = document.querySelector('input[name="monthly_subscription"]');

        membershipTiers.forEach(tier => {
            tier.addEventListener('change', function() {
                if (this.value !== 'custom') {
                    customAmountInput.value = this.value;
                } else {
                    customAmountInput.value = '';
                }
            });
        });
    });
</script>

@endsection