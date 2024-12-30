<div class="row">
    <div class="col-xs-12 col-md-8 col-md-offset-2 pull-left">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title">There is a problem with your subscription</h3>
            </div>
            <div class="panel-body">
                @if ($user->payment_method == 'gocardless')
                    @if (!empty($user->subscription_id))
                    <p>
                        Your Direct Debit payment has failed and we need you to make a manual payment.<br />
                        Please start by migrating to the new direct debit system.<br />
                        This process wont charge you anything, just setup the new Direct Debit.
                    </p>
                    <p>
                        {!! Form::open(array('method'=>'POST', 'route' => ['account.payment.gocardless-migrate'])) !!}
                        {!! Form::submit('Setup a variable Direct Debit', array('class'=>'btn btn-primary')) !!}
                        {!! Form::close() !!}
                    </p>
                    @else
                    <p>
                        Something odd is going on as you shouldn't see this message. Please let a board know there is an issue with your membership.
                    </p>
                    @endif
                @elseif ($user->payment_method == 'gocardless-variable')
                    <p>
                        Your latest subscription payment has failed and your account has been temporarily suspended.<br />
                        You can retry your payment now.

                        <div class="paymentModule" data-reason="subscription" data-display-reason="Retry payment" data-methods="gocardless" data-amount="{{ $user->monthly_subscription }}"></div>

                        @if (Auth::user()->isAdmin())
                            <small>Admins: You cannot do this process on behalf of the member, it will just charge your account.</small>
                        @endif
                    </p>
                @else
                <p>
                    There is a problem with your subscription payment,
                    this could be because you have cancelled, are in the process of switching payment methods,
                    have missed a monthly payment or it may be because bank records haven't been reconciled yet.<br />
                    If you know you have missed a payment please make this ASAP or ideally change over to a direct debit payment.<br />
                    If you have concerns or aren't sure please contact a board.<br />
                    <br />
                    <a href="{{ route('account.subscription.create', $user->id) }}" class="btn btn-primary">Setup a Direct Debit for &pound;{{ round($user->monthly_subscription) }}</a>
                    <small><a href="#" class="js-show-alter-subscription-amount">Change your monthly amount</a></small>
                    {!! Form::open(array('method'=>'POST', 'class'=>'form-inline hidden js-alter-subscription-amount-form', 'style'=>'display:inline-block', 'route' => ['account.update-sub-payment', $user->id])) !!}
                    <div class="input-group">
                        <div class="input-group-addon">&pound;</div>
                        {!! Form::input('number', 'monthly_subscription', round($user->monthly_subscription), ['class' => 'form-control', 'placeholder' => MembershipPayments::getRecommendedPrice() / 100, 'min' => MembershipPayments::getMinimumPrice() / 100, 'step' => '1']) !!}
                    </div>
                    {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                    {!! Form::close() !!}
                </p>
                @endif

            </div>
        </div>
    </div>
</div>
