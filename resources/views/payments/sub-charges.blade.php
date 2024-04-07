@extends('layouts.main')

@section('meta-title')
Payments - Sub Charges
@stop

@section('page-title')
Payments - Sub Charges
@stop

@section('main-tab-bar')
    @include('payments.partials.tabs')
@stop

@section('content')

{!! $charges->render() !!}
<table class="table memberList">
    <thead>
        <tr>
            <th>Charge Date</th>
            <th>Member</th>
            <th>Payment Date</th>
            <th>Status</th>
            <th>Amount</th>
            <th>Method</th>
        </tr>
    </thead>
    <tbody>
        @each('payments.sub-charges-row', $charges, 'charge')
    </tbody>
</table>

{!! $charges->render() !!}

@stop
