@use('BB\Support\SubscriptionChargeStatus')

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Monthly Subscription Charges</h3>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th>Charge Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($subscriptionCharges as $charge)
        <tr class="{{ match ($charge->status) { SubscriptionChargeStatus::Pending => '', SubscriptionChargeStatus::Due => 'warning', SubscriptionChargeStatus::Processing => 'info', SubscriptionChargeStatus::Paid => 'success', SubscriptionChargeStatus::Cancelled => 'text-muted' } }}">
            <td>{{ $charge->charge_date->toFormattedDateString() }}</td>
            <td>{{ $charge->amount_label }}</td>
            <td>{{ $charge->status_label }}</td>
            <td>{{ $charge->payment_date_label }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="panel-footer">
    {!! $subscriptionCharges->render() !!}
    </div>
</div>