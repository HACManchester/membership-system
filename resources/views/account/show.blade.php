@extends('layouts.main')

@section('meta-title')
{{ $user->name }} - Manage your membership
@stop

@section('page-title')
    {{ $user->name }}<br />
    <small>{{ $user->email }}</small>
@stop


@section('page-key-image')
    @include('partials.components.member-photo', ['profileData' => $user->profile, 'userHash' => $user->hash, 'size' => 100, 'class' => ''])
@stop


@section('page-action-buttons')
    <a class="btn btn-secondary" href="{{ route('account.edit', [$user->id]) }}"><i class="material-icons">mode_edit</i> Edit</a>
    <a class="btn btn-secondary" href="{{ route('members.show', [$user->id]) }}"><i class="material-icons">person</i> View Profile</a>
@stop

@section('content')

@include('account.partials.member-status-bar')

@include('account.partials.alerts')

@include('account.partials.member-admin-action-bar')

{{-- These have equal page priority, but end up being mutually exclusive --}}
@include('account.partials.get-started')
@include('account.partials.online-only-upsell')

@if ($user->status == 'left')
    @include('account.partials.rejoin')
@endif
    
@if ($user->status == 'leaving')
    @include('account.partials.leaving-warning')
@endif

@if ($user->isSuspended())
    @include('account.partials.payment-problem-panel')
@endif


@if ($user->status != 'setting-up')
    {{-- Call to action / signposting section --}}
    @include('account.partials.signposts.list')

    @if (!$user->isSuspended())
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                @include('account.partials.induction-panel')
            </div>
        </div>
    @endif

    @if ($user->status != 'honorary')
        <div class="row">
            <div class="col-xs-12 col-lg-12 pull-left">
                @include('account.partials.sub-charges')
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-lg-12 pull-left">
                @include('account.partials.payments-panel')
            </div>
        </div>

        @if (($user->status != 'left') && ($user->status != 'leaving'))
        <div class="row">
            <div class="col-xs-12 col-lg-4">
                @include('account.partials.cancel-panel')
            </div>
        </div>
        @endif
    @endif
@endif


@include('account.partials.change-subscription-modal')

@stop
