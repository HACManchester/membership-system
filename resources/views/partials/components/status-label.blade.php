@use('BB\Support\MemberStatus')
@if ($status == MemberStatus::SettingUp)
    <span class="label label-warning" data-toggle="tooltip" data-placement="top" title="We are waiting for a subscription to be setup">Setting Up</span>
@elseif ($status == MemberStatus::Active)
    <span class="label label-success">Active</span>
@elseif ($status == MemberStatus::PaymentWarning)
    <span class="label label-danger" data-toggle="tooltip" data-placement="top" title="There is something wrong with your subscription">Payment Warning</span>
@elseif ($status == MemberStatus::Leaving)
    <span class="label label-default" data-toggle="tooltip" data-placement="top" title="You're leaving and will lose access when your payment expires">Leaving</span>
@elseif ($status == MemberStatus::OnHold)
    <span class="label label-default">On Hold</span>
@elseif ($status == MemberStatus::Left)
    <span class="label label-default">Left</span>
@elseif ($status == MemberStatus::Honorary)
    <span class="label label-default">Honorary</span>
@elseif ($status == MemberStatus::Suspended)
    <span class="label label-default" data-toggle="tooltip" data-placement="top" title="Your payment has failed, please make a manual payment to reactivate your account.">Suspended</span>
@endif
