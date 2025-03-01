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

    <form action="{{ route('account.store') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="online_only" value="0">

        @if($gift)
            <input type="hidden" name="gift_code" value="{{ $gift_code }}">
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

        <fieldset class="col-md-8">
            <legend>Login details</legend>
            <div class="form-group {{ FlashNotification::hasErrorDetail('email', 'has-error has-feedback') }}">
                <label for="email" class="col-sm-3 control-label">Email</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="email" name="email" id="email" class="form-control" autocomplete="email" required value="{{ old('email') }}">
                    {!! FlashNotification::getErrorDetail('email') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('password', 'has-error has-feedback') }}">
                <label for="password" class="col-sm-3 control-label">Password</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="password" name="password" id="password" class="form-control" required>
                    {!! FlashNotification::getErrorDetail('password') !!}
                </div>
            </div>
        </fieldset>

        <fieldset class="col-md-8">
            <legend>Profile Information</legend>
            <div class="form-group {{ FlashNotification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
                <label for="display_name" class="col-sm-3 control-label">Username</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="display_name" id="display_name" class="form-control" autocomplete="display-name" required value="{{ old('display_name') }}">
                    {!! FlashNotification::getErrorDetail('display_name') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('pronouns', 'has-error has-feedback') }}">
                <label for="pronouns" class="col-sm-3 control-label">Pronouns (optional)</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="pronouns" id="pronouns" class="form-control" value="{{ old('pronouns') }}">
                    {!! FlashNotification::getErrorDetail('pronouns') !!}
                    <span class="help-block">We want everybody to feel welcome at Hackspace Manchester. If you would like to share your pronouns on your profile, you can provide them here.</span>
                </div>
            </div>
        </fieldset>

        <fieldset class="col-md-12">
            <legend>Membership Payment</legend>
            @if($gift_valid)
                <div class="alert alert-success">
                    As you have a gift certificate applied, <b>you won't pay for the duration of your free membership</b>. 
                </div>
            @endif

            <div class="form-group {{ FlashNotification::hasErrorDetail('monthly_subscription', 'has-error has-feedback') }}">
                {!! FlashNotification::getErrorDetail('membership_tier') !!}
                <div>
                    @foreach($priceOptions as $option)
                        <div class="col-sm-3">
                            <div class="panel panel-default" onclick="document.getElementById('subscription_{{ $option->value_in_pence }}').click()">
                                <div class="panel-heading">
                                    <input type="radio" name="membership_tier" 
                                           value="{{ $option->value_in_pence / 100 }}" 
                                           id="subscription_{{ $option->value_in_pence }}" 
                                           class="form-check-input"
                                           {{ old('membership_tier', $recommendedAmount) == $option->value_in_pence ? 'checked' : '' }}>
                                    <label for="subscription_{{ $option->value_in_pence }}" class="form-check-label">
                                        {{ $option->title }}: £{{ number_format($option->value_in_pence / 100, 2) }}
                                    </label>
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
                                <input type="radio" name="membership_tier" value="custom" id="subscription_custom" class="form-check-input" {{ old('membership_tier') === 'custom' ? 'checked' : '' }}>
                                <label for="subscription_custom" class="form-check-label">Custom Amount</label>
                            </div>
                            <div class="panel-body">
                                <p>For those that want to go above and beyond to support the makerspace, enter a custom amount here:</p>
                                <label for="monthly_subscription">Custom Amount</label>
                                <input type="number" name="monthly_subscription" id="monthly_subscription" 
                                       class="form-control" placeholder="Enter amount" 
                                       min="{{ $minAmount / 100 }}" step="1" 
                                       value="{{ old('monthly_subscription', $recommendedAmount / 100) }}">
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
        </fieldset>

        <fieldset class="col-md-8">
            <legend>Contact details</legend>
            
            <p>
                As a membership organization, we are legally obliged to maintain accurate records of our members
                (<a href="https://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general" target="_blank">
                    Companies Act 2006
                </a>).
                Please provide truthful and up-to-date information, and notify us within 2 months if any of this information changes.
            </p>
            
            <div class="form-group {{ FlashNotification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
                <label for="given_name" class="col-sm-3 control-label">First Name</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="given_name" id="given_name" class="form-control" autocomplete="given-name" required value="{{ old('given_name') }}">
                    {!! FlashNotification::getErrorDetail('given_name') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
                <label for="family_name" class="col-sm-3 control-label">Surname</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="family_name" id="family_name" class="form-control" autocomplete="family-name" required value="{{ old('family_name') }}">
                    {!! FlashNotification::getErrorDetail('family_name') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('suppress_real_name', 'has-error has-feedback') }}">
                <label for="suppress_real_name" class="col-sm-3 control-label">Real name privacy</label>
                <div class="col-sm-9 col-lg-7">
                    <p class="help-block">We understand some members are privacy conscious and may wish to keep their real name private from others in the community.</p>
                    <div>
                        <label>
                            <input type="radio" name="suppress_real_name" value="0" {{ old('suppress_real_name') == '0' ? 'checked' : '' }}>
                            Yes, my real name may be shared with others
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="radio" name="suppress_real_name" value="1" {{ old('suppress_real_name') == '1' ? 'checked' : '' }}>
                            No, I'd like to keep my real name private
                        </label>
                    </div>
                    {!! FlashNotification::getErrorDetail('suppress_real_name') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
                <label for="address_line_1" class="col-sm-3 control-label">Address Line 1</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="address[line_1]" id="address_line_1" class="form-control" autocomplete="address-line1" required value="{{ old('address.line_1') }}">
                    {!! FlashNotification::getErrorDetail('address.line_1') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
                <label for="address_line_2" class="col-sm-3 control-label">Address Line 2</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="address[line_2]" id="address_line_2" class="form-control" autocomplete="address-line2" value="{{ old('address.line_2') }}">
                    {!! FlashNotification::getErrorDetail('address.line_2') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
                <label for="address_line_3" class="col-sm-3 control-label">Address Line 3</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="address[line_3]" id="address_line_3" class="form-control" autocomplete="address-level2" value="{{ old('address.line_3') }}">
                    {!! FlashNotification::getErrorDetail('address.line_3') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
                <label for="address_line_4" class="col-sm-3 control-label">Address Line 4</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="address[line_4]" id="address_line_4" class="form-control" autocomplete="address-level1" value="{{ old('address.line_4') }}">
                    {!! FlashNotification::getErrorDetail('address.line_4') !!}
                </div>
            </div>

            <div class="form-group {{ FlashNotification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
                <label for="address_postcode" class="col-sm-3 control-label">Post Code</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="address[postcode]" id="address_postcode" class="form-control" autocomplete="postal-code" required value="{{ old('address.postcode') }}">
                    {!! FlashNotification::getErrorDetail('address.postcode') !!}
                </div>
            </div>
            
            <div class="form-group {{ FlashNotification::hasErrorDetail('phone', 'has-error has-feedback') }}">
                <label for="phone" class="col-sm-3 control-label">Phone</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="tel" name="phone" id="phone" class="form-control" autocomplete="tel" required value="{{ old('phone') }}">
                    {!! FlashNotification::getErrorDetail('phone') !!}
                </div>
            </div>
        </fieldset>

        <fieldset class="col-md-8">
            <legend>Emergency Contact Details</legend>

            <div class="form-group {{ FlashNotification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
                <label for="emergency_contact" class="col-sm-3 control-label">Emergency Contact</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" required value="{{ old('emergency_contact') }}">
                    {!! FlashNotification::getErrorDetail('emergency_contact') !!}
                    <span class="help-block">Please give us the name and contact details of someone we can contact if needed.</span>
                </div>
            </div>
        </fieldset>

        <fieldset class="col-md-8">
            <legend>Agreements</legend>

            <div class="alert alert-warning">
                <h4>Pacemaker warning</h4>

                <p>
                    Some of our tools may pose a risk to those that have pacemakers fitted. This risk may be especially high around
                    the welding equipment. Please consult your doctor about the risks of using machinery when wearing a pacemaker.
                    If you do undergo an induction on any tools that carry a risk, please mention this to your trainer.
                </p>
            </div>

            <p>
                We want Hackspace Manchester to be a welcoming and inclusive environment, where everybody feels comfortable and
                behaves safely. Please familiarise yourself with
                <a href="https://hacman.org.uk/rules" target="_blank">our rules and code of conduct</a>.
            </p>

            <div class="form-group {{ FlashNotification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
                <div class="col-xs-12">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="rules_agreed" value="1" {{ old('rules_agreed') ? 'checked' : '' }}>
                            I agree to the Hackspace Manchester rules and code of conduct
                        </label>
                        {!! FlashNotification::getErrorDetail('rules_agreed') !!}
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-xs-12">
                <button type="submit" class="btn btn-primary">Join Hackspace Manchester</button>
            </div>
        </div>

    </form>

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