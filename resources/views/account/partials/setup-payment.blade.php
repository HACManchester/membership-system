<h3>💳 Set up a direct debit</h3>
<p>Not able to pay by direct debit? Email the board, board@hacman.org.uk</p>

<a href="{{ route('account.subscription.create', $user->id) }}" class="btn btn-primary">Setup a Direct Debit for &pound;{{ round($user->monthly_subscription) }}</a>
<small><a href="#" class="js-show-alter-subscription-amount">Change your monthly direct debit amount</a></small>
{!! Form::open(array('method'=>'POST', 'class'=>'form-inline hidden js-alter-subscription-amount-form', 'style'=>'display:inline-block', 'route' => ['account.update-sub-payment', $user->id])) !!}
<div class="input-group">
    <div class="input-group-addon">&pound;</div>
    {!! Form::input('number', 'monthly_subscription', round($user->monthly_subscription), ['class' => 'form-control', 'placeholder' => MembershipPayments::getRecommendedPrice() / 100, 'min' => MembershipPayments::getMinimumPrice() / 100, 'step' => '1']) !!}
</div>
{!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
{!! Form::close() !!}
<br /><br />
<p>
    Your subscription payments will be taken on the day you complete this process unless stated otherwise on this page.<br />
    You can cancel the Direct Debit at any point through this website or your bank giving you full control over the payments.
    It will also protected by the <a href="https://gocardless.com/direct-debit/guarantee/" target="_blank">Direct Debit guarantee.</a>
</p>
