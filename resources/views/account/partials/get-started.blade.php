<?php
$conditions = [
    $user->payment_method,
    $user->induction_completed,
    $user->keyFob()
];

$countOfConditions = count($conditions);
$countOfCompletedConditions = count(array_filter($conditions));

$pctComplete = 100 / $countOfConditions * $countOfCompletedConditions;
$fmtMonthlySubscription = number_format($user->monthly_subscription, 2);
?>

@if(!$user->online_only && $countOfConditions !== $countOfCompletedConditions)
    <div class="row">
        <div class="col-xs-12 col-lg-6">
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
                            'number' => '2',
                            'condition' => $user->induction_completed,
                            'title' => 'Read through our General Induction',
                            'link' => route('account.induction.show', $user->id),
                            'description' => 'Our General Induction includes important information about being a member, how to get to the Hackspace, and other important information.'
                        ])
                        @include('account.partials.get-started-checklist-item', [
                            'number' => '3',
                            'condition' => $user->keyFob(),
                            'title' => 'Visit the Hackspace to finish setting up',
                            'link' => 'https://docs.hacman.org.uk/open_evenings/',
                            'link_target' => '_blank',
                            'description' => 'Come along on one of our open evenings for an introduction to the space, and to set up your 24/7 access fob.'
                        ])
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif