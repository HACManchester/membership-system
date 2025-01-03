@extends('layouts.main')

@section('meta-title')
    Balances overview
@stop

@section('page-title')
    Balances overview
@stop

@section('main-tab-bar')
    @include('payments.partials.tabs')
@stop

@section('content')
    <table class="table memberList">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th colspan="2">In credit</th>
                <th colspan="2">In debt</th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <th>No. users</th>
                <th>Sum of balances</th>
                <th>No. users</th>
                <th>Sum of balances</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Active members</th>
                <td>{{ $activeUsersInCreditQty }}</td>
                <td>&pound;{{ number_format($activeUsersInCreditSum, 2) }}</td>
                <td>{{ $activeUsersInDebtQty }}</td>
                <td>&pound;{{ number_format($activeUsersInDebtSum, 2) }}</td>
            </tr>
            <tr>
                <th>Left members</th>
                <td>{{ $inactiveUsersInCreditQty }}</td>
                <td>&pound;{{ number_format($inactiveUsersInCreditSum, 2) }}</td>
                <td>{{ $inactiveUsersInDebtQty }}</td>
                <td>&pound;{{ number_format($inactiveUsersInDebtSum, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table memberList">
        <thead>
            <tr>
                <th>Member name</th>
                <th>Member Status</th>
                <th>Balance status (in debt or in credit)</th>
                <th>Balance amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>
                        <a href="{{ route('account.show', $user->id) }}">{{ $user->name }}</a>
                    </td>
                    <td>{!! HTML::statusLabel($user->status) !!}</td>
                    <td>
                        @if ($user->cash_balance < 0)
                            <span class="label label-danger">In debt</span>
                        @else
                            <span class="label label-info">In credit</span>
                        @endif
                    </td>
                    <td>{{ $user->present()->cashBalance }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
