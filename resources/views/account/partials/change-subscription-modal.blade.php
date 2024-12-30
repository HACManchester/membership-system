<div class="modal fade" id="changeSubscriptionModel" tabindex="-1" role="dialog" aria-labelledby="changeSubscriptionModelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="changeSubscriptionModelLabel">Change subscription amount</h4>
            </div>
            <div class="modal-body">
                @if (!$user->canMemberChangeSubAmount() && $user->payment_method)
                    <p>Unfortunately we cannot change your subscription amount with your current payment provider. Please contact the board for assistance.</p>
                @else
                    {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-payment', $user->id])) !!}
                        <div class="form-group">
                            {!! Form::label('monthly_subscription', 'Monthly Subscription Amount', ['class'=>'control-label']) !!}
                            <div class="input-group">
                                <div class="input-group-addon">&pound;</div>
                                    {!! Form::input(
                                        'number',
                                        'monthly_subscription',
                                        round($user->monthly_subscription),
                                        [
                                            'class' => 'form-control',
                                            'placeholder' => MembershipPayments::getRecommendedPrice() / 100,
                                            'min' => Auth::user()->isAdmin() ? 0 : MembershipPayments::getMinimumPrice() / 100,
                                            'step' => '1'
                                        ]
                                    ) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                            </div>
                        <div class="clearfix"></div>
                    {!! Form::close() !!}

                    <p>If you're not sure how much to pay, here are some general guidelines to help you find a suitable subscription amount for your circumstances:</p>

                    <strong>Minimum {{ MembershipPayments::formatPrice(MembershipPayments::getMinimumPrice()) }} a month:</strong>
                    <ul>
                        <li>You are on a low income and unable to afford a higher amount.</li>
                    </ul>

                    <strong>{{ MembershipPayments::formatPrice(MembershipPayments::getRecommendedPrice()) }} a month:</strong>
                    <ul>
                        <li>You are planning to visit the makerspace regularly and are a professional / in full-time employment</li>
                    </ul>

                    <strong>&pound;25 a month and up:</strong>
                    <ul>
                        <li>You are planning to visit the makerspace regularly and would like to provide a little extra support (thank you!)</li>
                    </ul>

                    <p>
                        If you feel that the makerspace is worth more to you then please do adjust your subscription accordingly.
                        You can also change your subscription amount at any time!
                    </p>

                    <p>
                        If you would like to pay less than {{ MembershipPayments::formatPrice(MembershipPayments::getMinimumPrice()) }} a month 
                        please send the board an email letting them know how much you would like to pay, they will then override the amount
                        so you can continue to setup a subscription.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>