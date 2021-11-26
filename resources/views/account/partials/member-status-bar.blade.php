@if($user->gift)
    <div class="row">
        <div class="alert alert-success">
            <p>🎁 Free gift period.
                <a href="/account/0/edit#access">Get an instant access code</a> /
                <a href="https://t.me/hacmanchester" target="_blank">Join the group chat</a> /
                Set up payment before {!! $user->subscription_expires->toFormattedDateString() !!}
            </p>
        </div>
    </div>
@endif

<div class="alert alert-success">
    <div class="btn-group" role="group" aria-label="Basic example">
        @if($user->online_only)
            <a href="#" type="button" class="btn btn-warning">Online Only</a>
        @else
            <a href="#" type="button" class="btn">
                {!! HTML::statusLabel($user->status) !!}
            </a>
        @endif
        
        <a href="#" type="button" class="btn">
            {!! HTML::spaceAccessLabel($user->active) !!}
        </a>
            
        <a href="/account/0/edit#access" type="button" class="btn">
            🔑 {{ $user->keyFob() ? "✔️" : "❌" }}
        </a>

        @if (!$user->online_only)
            <a href="/account/0/balance" type="button" class="btn">
            💰 {{ $memberBalance }}
            </a>

            <a href="#" type="button" class="btn">
                💳 {{ $user->present()->subscriptionDetailLine }}
                @if ($user->canMemberChangeSubAmount())
                    <small><a href="#" class="js-show-alter-subscription-amount" title="Change Amount">Change</a></small>
                @endif
            </a>

                @if ($user->canMemberChangeSubAmount())
                    <div class="hidden js-alter-subscription-amount-form clearfix">
                        {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-payment', $user->id])) !!}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    {!! Form::text('monthly_subscription', round($user->monthly_subscription), ['class'=>'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}

                        @if (Auth::user()->isAdmin())

                            @if ($user->payment_method == 'gocardless-variable')
                                {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-method', $user->id])) !!}
                                    {!! Form::hidden('payment_method', 'balance') !!}
                                    {!! Form::submit('Change to balance payment', array('class'=>'btn btn-default')) !!}
                                    <p>This will try and take their monthly subscription from the members balance</p>
                                {!! Form::close() !!}
                            @endif

                            @if ($user->payment_method == 'balance')
                                {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-method', $user->id])) !!}
                                {!! Form::hidden('payment_method', 'gocardless-variable') !!}
                                {!! Form::submit('Change to DD payment', array('class'=>'btn btn-default')) !!}
                                <p>This switches back to a variable DD or resets the payment method if one doesn't exist</p>
                                {!! Form::close() !!}
                            @endif

                        @endif
                    </div>
                @endif
            @endif
    </div>
</div>
