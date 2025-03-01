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
                    <form method="POST" action="{{ route('account.update-sub-payment', $user->id) }}" class="" style="margin-bottom:20px;">
                        @csrf
                        <div class="form-group">
                            <div>
                                @foreach(MembershipPayments::getPriceOptions() as $option)
                                    <div class="panel panel-default" onclick="document.getElementById('subscription_{{ $option->value_in_pence }}').click()">
                                        <div class="panel-heading">
                                            <input type="radio" name="membership_tier" value="{{ $option->value_in_pence / 100 }}" 
                                                  id="subscription_{{ $option->value_in_pence }}" class="form-check-input"
                                                  {{ $option->value_in_pence == $user->monthly_subscription * 100 ? 'checked' : '' }}>
                                            <label for="subscription_{{ $option->value_in_pence }}" class="form-check-label">
                                                {{ $option->title }}: £{{ number_format($option->value_in_pence / 100, 2) }}
                                            </label>
                                        </div>
                                        <div class="panel-body">
                                            <p>{!! nl2br($option->description) !!}</p>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="panel panel-default" onclick="document.getElementById('subscription_custom').click()">
                                    <div class="panel-heading">
                                        <input type="radio" name="membership_tier" value="custom" id="subscription_custom" class="form-check-input">
                                        <label for="subscription_custom" class="form-check-label">Custom Amount</label>
                                    </div>
                                    <div class="panel-body">
                                        <p>For those that want to go above and beyond to support the makerspace, enter a custom amount here:</p>
                                        <label for="custom_subscription_amount" class="form-check-label">Custom Amount</label>
                                        <input type="number" name="monthly_subscription" value="{{ $user->monthly_subscription }}" 
                                              class="form-control" placeholder="Enter amount" 
                                              min="{{ MembershipPayments::getMinimumPrice() / 100 }}" step="1">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-default">Update</button>
                            </div>
                        <div class="clearfix"></div>
                    </form>

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