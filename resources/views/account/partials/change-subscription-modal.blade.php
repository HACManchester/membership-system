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
                            <div>
                                @foreach(MembershipPayments::getPriceOptions() as $option)
                                    <div class="panel panel-default" onclick="document.getElementById('subscription_{{ $option->value_in_pence }}').click()">
                                        <div class="panel-heading">
                                            {!! Form::radio('membership_tier', $option->value_in_pence / 100, $option->value_in_pence == $user->monthly_subscription * 100, ['class' => 'form-check-input', 'id' => 'subscription_' . $option->value_in_pence]) !!}
                                            {!! Form::label('subscription_' . $option->value_in_pence, $option->title . ': £' . number_format($option->value_in_pence / 100, 2), ['class' => 'form-check-label']) !!}
                                        </div>
                                        <div class="panel-body">
                                            <p>{!! nl2br($option->description) !!}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="panel panel-default" onclick="document.getElementById('subscription_custom').click()">
                                    <div class="panel-heading">
                                        {!! Form::radio('membership_tier', 'custom', false, ['class' => 'form-check-input', 'id' => 'subscription_custom']) !!}
                                        {!! Form::label('subscription_custom', 'Custom Amount', ['class' => 'form-check-label']) !!}
                                    </div>
                                    <div class="panel-body">
                                        <p>For those that want to go above and beyond to support the makerspace, enter a custom amount here:</p>
                                        {!! Form::label('custom_subscription_amount', 'Custom Amount', ['class' => 'form-check-label']) !!}
                                        {!! Form::input('number', 'monthly_subscription', $user->monthly_subscription, ['class' => 'form-control', 'placeholder' => 'Enter amount', 'min' => MembershipPayments::getMinimumPrice() / 100, 'step' => '1']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                            </div>
                        <div class="clearfix"></div>
                    {!! Form::close() !!}

                    <p>
                        If you would like to pay less than {{ MembershipPayments::formatPrice(MembershipPayments::getMinimumPrice()) }} a month 
                        please email <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a>.
                    </p>
                @endif
            </div>
        </div>
    </div>
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