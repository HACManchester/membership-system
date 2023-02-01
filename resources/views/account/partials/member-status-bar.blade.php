<div class="member-status-bar">
    @if($user->online_only)
        <div>
            <span class="label label-warning">Online Only</span>
        </div>
    @else
        <div>
            {!! HTML::statusLabel($user->status) !!}
        </div>
    @endif
    
    <div>
        {!! HTML::spaceAccessLabel($user->active) !!}
    </div>

    @if (!$user->online_only)
        <a href="/account/{{ $user->id }}/balance">
            💰 Balance: {{ $memberBalance }}
        </a>

        @if($user->payment_method)
            <div>
                💳 Subscription: {{ $user->present()->subscriptionDetailLine }}
                @if ($user->canMemberChangeSubAmount())
                    <button type="button" class="btn btn-link btn-xs" data-toggle="modal" data-target="#changeSubscriptionModel">
                        Change
                    </button>
                @endif
            </div>
        @endif

        @if ($user->canMemberChangeSubAmount())
            <div class="modal fade" id="changeSubscriptionModel" tabindex="-1" role="dialog" aria-labelledby="changeSubscriptionModelLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="changeSubscriptionModelLabel">Change subscription amount</h4>
                        </div>
                        <div class="modal-body">
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
                                <div>
                                    <h2>Admin options</h2>
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
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    
        <div>
            @if ($user->keyFob())
                
            <a href="/account/{{ $user->id }}/edit#access">
                    🔑 {{ $user->keyFobs()->count() }} access {{ str_plural('method', $user->keyFobs()->count() )}}
                </a>
            @else
                <a href="/account/{{ $user->id }}/edit#access">
                    🔑 Set up an access method
                </a>
            @endif
        </div>
    @endif
</div>

@if($user->gift)
    <div class="alert alert-success">
        <p>🎁 Free gift period. 
            Expires {!! $user->subscription_expires->toFormattedDateString() !!} - set up payment to keep access from this date.
        </p>
    </div>
@endif