@if ($status == 'setting-up')
    <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="We are waiting for a subscription to be setup">Setting Up</span>
@elseif ($status == 'active')
    <span class="label label-success">Active</span>
@elseif ($status == 'payment-warning')
    <span class="label label-danger" data-toggle="tooltip" data-placement="top" title="There is something wrong with your subscription">Payment Warning</span>
@elseif ($status == 'leaving')
    <span class="label label-default" data-toggle="tooltip" data-placement="top" title="Your leaving and will loose access when your payment expires">Leaving</span>
@elseif ($status == 'on-hold')
    <span class="label label-default">On Hold</span>
@elseif ($status == 'left')
    <span class="label label-default">Left</span>
@elseif ($status == 'honorary')
    <span class="label label-default">Honorary</span>
@elseif ($status == 'suspended')
    <span class="label label-default" data-toggle="tooltip" data-placement="top" title="Your payment has failed, please make a manual payment to reactivate your account.">Suspended</span>
@endif
