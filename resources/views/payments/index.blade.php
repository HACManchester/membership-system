@extends('layouts.main')

@section('meta-title')
Payments
@stop

@section('page-title')
Payments
@stop

@section('page-action-buttons')
@stop

@section('main-tab-bar')
    @include('payments.partials.tabs')
@stop

@section('content')

<div class="row">
    <div class="col-xs-12 well">
        <form method="GET" action="{{ route('payments.index') }}" class="navbar-form navbar-left">
            <select name="date_filter" class="form-control js-advanced-dropdown" style="margin-right:10px; width:150px;">
                <option value="">All Time</option>
                @foreach($dateRange as $value => $label)
                    <option value="{{ $value }}" {{ Request::get('date_filter', '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="member_filter" class="form-control js-advanced-dropdown" style="margin-right:10px; width:150px;">
                <option value="">All Members</option>
                @foreach($memberList as $id => $name)
                    <option value="{{ $id }}" {{ Request::get('member_filter', '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="reason_filter" class="form-control js-advanced-dropdown" style="margin-right:10px; width:150px;">
                <option value="">All Reasons</option>
                @foreach($reasonList as $id => $name)
                    <option value="{{ $id }}" {{ Request::get('reason_filter', '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-default btn-sm">Filter</button>
        </form>
    </div>
</div>

@include('partials.components.paginator-links', ['collection' => $payments])
<table class="table memberList">
    <thead>
        <tr>
            <th>@include('partials.components.sort-by', ['column' => 'created_at', 'body' => 'Date', 'route' => 'payments.index'])</th>
            <th>Member</th>
            <th>@include('partials.components.sort-by', ['column' => 'reason', 'body' => 'Reason', 'route' => 'payments.index'])</th>
            <th>@include('partials.components.sort-by', ['column' => 'source', 'body' => 'Method', 'route' => 'payments.index'])</th>
            <th>@include('partials.components.sort-by', ['column' => 'amount', 'body' => 'Amount', 'route' => 'payments.index'])</th>
            <th>@include('partials.components.sort-by', ['column' => 'reference', 'body' => 'Reference', 'route' => 'payments.index'])</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @each('payments.index-row', $payments, 'payment')
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" align="right">Total</td>
            <td><strong>&pound;{{ number_format($paymentTotal, 2) }}</strong></td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>

@include('partials.components.paginator-links', ['collection' => $payments])

    <div id="react-test"></div>

@stop

@section('footer-js')
    <script>

    </script>
@stop