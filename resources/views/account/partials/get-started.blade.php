<?php
$conditions = [
    $user->email_verified,
    $user->payment_method,
    $user->induction_completed,
    $user->keyFob(),
    $user->visited_forum,
];

$countOfConditions = count($conditions);
$countOfCompletedConditions = count(array_filter($conditions));

$pctComplete = 100 / $countOfConditions * $countOfCompletedConditions;
$fmtMonthlySubscription = number_format($user->monthly_subscription, 2);
?>
@if(!$user->online_only && !in_array($user->status, ['leaving', 'left', 'suspended']) && $countOfConditions !== $countOfCompletedConditions)
    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2 pull-left">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Getting Started</h3>
                </div>
                <div class="panel-body">
                    <p>Welcome to Hackspace Manchester! Let's get your membership set up.</p>

                    <div class="progress progress-rounded progress-bordered progress-bordered-success">
                        <div class="progress-bar progress-bar-success" role="progressbar" style="width: {{ max(1, $pctComplete)  }}%">
                        </div>
                    </div>

                    <ul class="get-started-checklist">
                        @include('account.partials.get-started-checklist-item', [
                            'number' => '1',
                            'condition' => $user->email_verified,
                            'title' => 'Verify your email address',
                            'link' =>  route('account.send-confirmation-email') ,
                            'description' => 'Please verify your e-mail address to make sure we have up-to-date contact details for you.'
                        ])
                        @include('account.partials.get-started-checklist-item', [
                            'number' => '2',
                            'condition' => $user->payment_method,
                            'title' => 'Set up your membership payment via Direct Debit',
                            'link' => route('account.subscription.create', $user->id),
                            'rawDescription' => <<<DESC
                                <p>
                                    Visit our payment provider to set up your monthly membership payment of &pound;{$fmtMonthlySubscription}
                                    (<button class="btn btn-link" data-toggle="modal" data-target="#changeSubscriptionModel">Change amount</button>).
                                </p>
                                <p>Payments will be taken monthly from the date you complete this step.</p>
                                <p>
                                    All payments will be protected by the
                                    <a href="https://gocardless.com/direct-debit/guarantee/" target="_blank">Direct Debit guarantee</a>,
                                    and you will be able to cancel at any point through this website or through your bank.
                                </p>
DESC
                        ])
                        @include('account.partials.get-started-checklist-item', [
                            'number' => '3',
                            'condition' => $user->induction_completed,
                            'title' => 'Get a General Induction',
                            'link' => route('general-induction.show', $user->id),
                            'description' => 'Come along to an open evening and get a General Induction which includes a tour and important information about being a member.'
                        ])
                        @include('account.partials.get-started-checklist-item', [
                            'number' => '4',
                            'condition' => $user->keyFob(),
                            'title' => 'Get an access method set up',
                            'link' => route('keyfobs.index', $user->id),
                            'description' => 'Set up your fob or access code for 24/7 access to the Hackspace.'
                        ])
                        @include('account.partials.get-started-checklist-item', [
                            'number' => '5',
                            'condition' => $user->visited_forum,
                            'title' => 'Visit our forum & get involved',
                            'link' => route('links.forum'),
                            'link_target' => '_blank',
                            'description' => 'Please visit the forum and get involved with discussions, lend your support to purchase proposals, and find out about upcoming events.'
                        ])
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif