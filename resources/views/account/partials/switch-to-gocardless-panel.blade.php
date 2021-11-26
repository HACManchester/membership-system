<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title">Your Payment Method</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <p class="lead">
                    @if($user->gift)
                        🎁 Want to continue using the space after your free gift period?
                    @else
                        Will you switch to a monthly Direct Debit?
                    @endif
                </p>
                <div>
                    @if($user->gift)
                        Once your free gift period ends ({!! $user->subscription_expires !!}) you won't have access to the space unless you set up a regular payment.
                        <br/>
                        The good news is it's super easy and secure to do so - your first payment will go out at the end of your free membership period.
                    @else
                        Processing the standing order payments each month takes up member time and introduces delays.<br />
                        A monthly Direct Debit payment is quick and painless and if you need you can cancel the payment from here or from your bank.<br />
                        <br />
                    @endif
                    It only takes a minute, just follow the link below to the 🔒<a href="https://gocardless.com/security" target="_blank">GoCardless</a> website (our payment processor) and complete the form.<br />
                    <br />
                    <a href="{{ route('account.subscription.create', $user->id) }}" class="btn btn-primary">🔒 Setup a Direct Debit for &pound;{{ round($user->monthly_subscription) }}</a>
                    <small><a href="#" class="js-show-alter-subscription-amount">Change your monthly direct debit amount</a></small>
                    {!! Form::open(array('method'=>'POST', 'class'=>'form-inline hidden js-alter-subscription-amount-form', 'style'=>'display:inline-block', 'route' => ['account.update-sub-payment', $user->id])) !!}
                    <div class="input-group">
                        <div class="input-group-addon">&pound;</div>
                        {!! Form::text('monthly_subscription', round($user->monthly_subscription), ['class'=>'form-control']) !!}
                    </div>
                    {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                    {!! Form::close() !!}
                    <br />
                    <p>
                        <small>By switching you will also protected by the <a href="https://gocardless.com/direct-debit/guarantee/">Direct Debit guarantee.</a></small><br />
                        <br  />
                        Don't forget to cancel your current subscription payment.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

