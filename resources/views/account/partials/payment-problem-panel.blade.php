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
                        <form method="POST" action="{{ route('account.payment.gocardless-migrate') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Setup a variable Direct Debit</button>
                        </form>
                    </p>
                    @else
                    <p>
                        Something odd is going on as you shouldn't see this message. Please let a board know there is an issue with your membership.
                    </p>
                    @endif
                @elseif ($user->payment_method == 'gocardless-variable')
                    <p>
                        Your latest subscription payment has failed.
                    </p>

                    @if ($hasSubscriptionPaymentsInProgress)
                        <div class="alert alert-warning">
                            <strong>Outstanding payment in progress:</strong> You have a payment being processed. 
                            Direct Debit payments take 3-5 business days to complete.
                        </div>
                        
                        <strong>While you wait, consider:</strong>
                        <ul class="mb-0">
                            <li>Check your bank account has sufficient funds</li>
                            <li>Verify your Direct Debit mandate hasn't been cancelled</li>
                            <li>If your bank details changed, set up a new Direct Debit below</li>
                        </ul>
                        
                        <p>
                            <a href="{{ route('account.subscription.create', $user->id) }}" class="btn btn-primary">
                                Set up new Direct Debit (if details changed)
                            </a>
                        </p>
                        
                    @else
                        <strong>Common reasons for payment failure:</strong>
                        <ul class="mb-0">
                            <li>Bank account closed or details changed</li>
                            <li>Insufficient funds on payment date</li>
                            <li>Direct Debit mandate cancelled at your bank</li>
                        </ul>
                        
                        <p>
                            <strong>Important:</strong> Direct Debit payments take 3-5 business days to process.
                        </p>
                        
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-sm-6">
                                <h5>Option 1: Retry Direct Debit</h5>
                                <p class="small text-muted">Use this if your bank details and balance are definitely correct.</p>
                                <div class="paymentModule" data-reason="subscription" data-display-reason="Retry payment" data-methods="gocardless" data-amount="{{ $user->monthly_subscription }}"></div>
                            </div>
                            <div class="col-sm-6">
                                <h5>Option 2: Set up new Direct Debit</h5>
                                <p class="small text-muted">Use this if your bank details have changed or you're unsure.</p>
                                <a href="{{ route('account.subscription.create', $user->id) }}" class="btn btn-primary">
                                    Set up new Direct Debit
                                </a>
                            </div>
                        </div>
                    @endif

                    @if (Auth::user()->isAdmin())
                        <div class="alert alert-danger" style="margin-top: 15px;">
                            <small><strong>Admins:</strong> You cannot do this process on behalf of the member, it will just charge your account.</small>
                        </div>
                    @endif
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
                    <form method="POST" action="{{ route('account.update-sub-payment', $user->id) }}" class="form-inline hidden js-alter-subscription-amount-form" style="display:inline-block">
                        @csrf
                        <div class="input-group">
                            <div class="input-group-addon">&pound;</div>
                            <input type="number" name="monthly_subscription" value="{{ round($user->monthly_subscription) }}" class="form-control" placeholder="{{ MembershipPayments::getRecommendedPrice() / 100 }}" min="{{ MembershipPayments::getMinimumPrice() / 100 }}" step="1">
                        </div>
                        <button type="submit" class="btn btn-default">Update</button>
                    </form>
                </p>
                @endif

            </div>
        </div>
    </div>
</div>
