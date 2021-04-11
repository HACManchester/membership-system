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
        <p>
            This is your Hackspace Manchester Balance, it can be used to pay for your time on the laser, storage boxes and in the future many other things as well.
        </p>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Add Credit</h3>
            </div>
            <div class="panel-body">
                <p>Top up using Direct Debit</p>

                <div class="paymentModule" data-reason="balance" data-display-reason="Balance Payment" data-button-label="Add Credit" data-methods="gocardless,stripe"></div>
<br></br>
                <p>
                    
                </p>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-6">
            <div class="panel panel-default">   
                <div class="panel-heading">
                    <h3 class="panel-title">Cash Topup</h3>
                </div>
                <div class="panel-body">
                <p>Use this if you are topping up with cash.</p>

{!! Form::open(['method'=>'POST', 'route' => ['account.payment.cash.create', $user->id], 'class'=>'form-horizontal']) !!}

<div class="form-group">
    <div class="col-sm-5">
        <div class="input-group">
            <div class="input-group-addon">&pound;</div>
            {!! Form::input('number', 'amount', '', ['class'=>'form-control', 'step'=>'0.01', 'min'=>'0', 'required'=>'required']) !!}
        </div>
    </div>
    <div class="col-sm-3">
        {!! Form::submit('Add Credit', array('class'=>'btn btn-primary')) !!}
        <br></br>
    </div>
            {!! Form::hidden('reason', 'balance') !!}
        {!! Form::hidden('return_path', 'account/'.$user->id)!!}
        {!! Form::close() !!}
                </div>
            </div>
        </div>
</div>
<div class="row">
    <div class="col-sm-12">
    <div class="panel panel-default text-center">
    <div class="panel-heading">
        <h3 class="panel-title">Snackspace Expenditure</h3>
        </div>
        <div class="panel-body">
        <div class="paymentModule" data-reason="snackspace" data-display-reason="Usage Fee" data-button-label="Buy Now" data-methods="balance" data-ref="snackspace"></div>

        </div>
<div class="row">
    <div class="col-sm-12">
    <div class="panel panel-default text-center">
    <div class="panel-heading">
        <h3 class="panel-title">Fob Purchase</h3>
        </div>
        <div class="panel-body">
        <div class="paymentModule" data-reason="Fob" data-display-reason="Usage Fee" data-button-label="Buy Now" data-methods="balance" data-ref="fob"></div>

        </div>
<div class="row">
    <div class="col-sm-12">
    <div class="panel panel-default text-center">
    <div class="panel-heading">
        <h3 class="panel-title">Balance</h3>
        </div>
        <div class="panel-body">
        <span class="credit-figure {{ $userBalanceSign }}">{{ $userBalance }}</span>
        </div>
    </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Balance Payment History</h3>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th>Reason</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($payments as $payment)
                <tr class="{{ $payment->present()->balanceRowClass }}">
                    <td>{{ $payment->present()->reason }}</td>
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