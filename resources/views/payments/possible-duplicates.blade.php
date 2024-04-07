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

<table class="table memberList">
    <thead>
        <tr>
            <th>Member</th>
            <th>Reason</th>
            <th>Amount</th>
            <th>Count</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($possibleDuplicates as $possibleDuplicate)
            <tr>
                <td><a href="{{ route('account.show', $possibleDuplicate->user->id) }}">{{ $possibleDuplicate->user->name }}</a></td>
                <td>{{ $possibleDuplicate->reason }}</td>
                <td>&pound;{{ number_format($possibleDuplicate->amount, 2) }}</td>
                <td>{{ $possibleDuplicate->count }}</td>
                <td>
                    {!! link_to_route('payments.index', 'View this member\'s payments', ['member_filter' => $possibleDuplicate->user->id], ['class'=>'btn btn-primary']) !!}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@stop
