<div class="alert alert-info" style="background:white; border-left: 2px solid">
    @if ($user->keyFob())
        @foreach ($user->keyFobs()->get() as $fob)
            @if (substr( $fob->key_id, 0, 2 ) == "ff")
                <a href="/account/{{ $user->id }}/edit#access" type="button" class="btn">
                    🔢 Access code: {{ str_replace('f', '', $fob->key_id) }}
                </a>
            @else
                <a href="/account/{{ $user->id }}/edit#access" type="button" class="btn">
                    🔑 Fob: {{ $fob->key_id }}
                </a>
            @endif
        @endforeach
    @else
        <a href="/account/{{ $user->id }}/edit#access" type="button" class="btn">
            🔑 Set up an access method
        </a>
    @endif
</div>
<div class="alert alert-info">
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

        @if (!$user->online_only)
            <a href="/account/{{ $user->id }}/balance" type="button" class="btn">
            💰 Balance: {{ $memberBalance }}
            </a>

            @if($user->payment_method)
            <a href="#" type="button" class="btn">
                💳 Subscription: {{ $user->present()->subscriptionDetailLine }}
                @if ($user->canMemberChangeSubAmount())
                    <small><a href="#" class="js-show-alter-subscription-amount" title="Change Amount">Change</a></small>
                @endif
            </a>
            @endif

                @if ($user->canMemberChangeSubAmount())
                    <div class="hidden js-alter-subscription-amount-form clearfix">
                        {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-payment', $user->id])) !!}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    {!! Form::input('number', 'monthly_subscription', round($user->monthly_subscription), ['class' => 'form-control', 'placeholder' => MembershipPayments::getRecommendedPrice() / 100, 'min' => MembershipPayments::getMinimumPrice() / 100, 'step' => '1']) !!}
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

@if($user->gift)
    <div class="alert alert-success">
        <p>🎁 Free gift period. 
            Expires {!! $user->subscription_expires->toFormattedDateString() !!} - set up payment to keep access from this date.
        </p>
    </div>
@endif