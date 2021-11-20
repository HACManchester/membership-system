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
            You can top up either by direct debit, or by putting cash in the pot in the space.
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
                <div>        
                    <span class="credit-figure" style="{{ $userBalance >= 0 ? 'color:#219724' : 'color:#D9534F' }}">{{ $userBalance }}</span>
                </div> 
            </div>
        </div>
    </div>
</div>

<h3>Add Credit</h3>
<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Top up with card or cash</h3>
            </div>
            <div class="panel-body">
                <p>Top Up Using Cash Topup or Direct Debit - select which you'd like from the dropdown.</p> <br>
                <p>Note: if you don't have a direct debit set up you should pay with cash</p>
                <div class="paymentModule" data-reason="balance" data-display-reason="Balance Payment" data-button-label="Add Credit" data-methods="gocardless,cash2"></div>
                <br>
                <p>
                    
                </p>
            </div>
        </div>
    </div>
</div>

<h3>Log Expenditure</h3>
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Snackspace Expenditure</h3>
            </div>
            <div class="panel-body">
                <div class="paymentModule" data-reason="snackspace" data-display-reason="Usage Fee" data-button-label="Buy Now" data-methods="balance" data-ref="snackspace"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Fob Purchase</h3>
            </div>
            <div class="panel-body">
                <div class="paymentModule" data-reason="Fob" data-display-reason="Usage Fee" data-button-label="Buy Now" data-methods="balance" data-ref="fob"></div>
            </div>
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