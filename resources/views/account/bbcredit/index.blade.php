@extends('layouts.main')

@section('meta-title')
Hackspace Manchester Balance {{ $user->name }}
@stop

@section('page-title')
Hackspace Manchester Balance
@stop

@section('content')

<div class="row">
    <div class="col-xs-12">
    <div class="panel panel-default">
    <div class="panel-body">

        <h4>
            Manage your Hackspace Balance, you can use it for purchasing snackspace items, paying for laser time, paying for materials that you use in the space.
</h4>
<br>
<p>
           We are looking to make the space cashless and your balance will be what you can use for paying for items. You will be able to top up using cash (in space only), direct debit.
</p>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-3 col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Your Balance</strong></h3>
            </div>
            <div class="panel-body">
                <p>Your Current Balance is:</p>

                <div>        <span class="credit-figure {{ $userBalanceSign }}">{{ $userBalance }}</span>
</div> <br>
                <p>
                    
                </p>
            </div>
        </div>
    </div>
<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Add Credit</strong></h3>
            </div>
            <div class="panel-body">
                <p>Top Up Using Cash Topup or Direct Debit via this option</p> <br>

                <div class="paymentModule" data-reason="balance" data-display-reason="Balance Payment" data-button-label="Add Credit" data-methods="gocardless,cash2"></div>
                <br>
                <p>
                    
                </p>
            </div>
        </div>
    </div>

    



<div class="row">
    <div class="col-xs-12">
    <div class="panel panel-default">
    <div class="panel-body">
                <h3 class="panel-title">Balance Payment History</h3>
            </div>
            <table class="table">
                <thead>
                <tr>
                <th class="not_mapped_style" style="text-align:left">Reason</th>
                    <th class="not_mapped_style" style="text-align:left">Method</th>
                    <th class="not_mapped_style" style="text-align:left">Date</th>
                    <th class="not_mapped_style" style="text-align:left">Amount</th>
                    <th class="not_mapped_style" style="text-align:left">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($payments as $payment)
                <tr class="{{ $payment->present()->balanceRowClass }}">
                    <td >{{ $payment->present()->reason }}</td>
                    <td>{{ $payment->present()->method }}</td>
                    <td>{{ $payment->present()->date }}</td>
                    <td>{{ $payment->present()->balanceAmount }}</td>
                    <td>{{ $payment->present()->status }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            <div class="panel-footer">
            {!! $payments->render() !!}
            </div>
        </div>
    </div>
</div>

@stop