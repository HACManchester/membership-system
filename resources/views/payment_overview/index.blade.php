@extends('layouts.main')

@section('meta-title')
Payment Stats
@stop

@section('page-title')
Payment Stats
@stop

@section('main-tab-bar')
    @include('payments.partials.tabs')
@stop

@section('content')

<div class="row">
    <div class="col-sm-12 col-md-6 col-xl-4">
        <div class="well">
            <h3 class="text-center">Storage Box Liability</h3>

            <p class="text-center">
                <span class="key-figure">{{ MembershipPayments::formatPrice((int) $storageBoxLiability) }}</span>
            </p>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-xl-4">
        <div class="well">
            <h3 class="text-center">Door Key Liability</h3>

            <p class="text-center">
                <span class="key-figure">{{ MembershipPayments::formatPrice((int) $doorKeyLiability) }}</span>
            </p>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-xl-4">
        <div class="well">
            <h3 class="text-center">Balance Liability</h3>

            <h4 class="text-center">Current</h4>
            <p class="text-center">
                <span class="key-figure">{{ MembershipPayments::formatSignedPrice((int) $balanceLiability) }}</span>
            </p>

            <h4 class="text-center">Paid In / Spent</h4>
            <p class="text-center">
                <span class="key-figure">{{ MembershipPayments::formatPrice((int) $balancePaidIn) }} / {{ MembershipPayments::formatPrice((int) $balancePaidOut) }}</span>
            </p>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-xl-4">
        <div class="well">
            <h3 class="text-center">Laser Cutter</h3>

            <h4 class="text-center">Initial Investment</h4>
            <p class="text-center">
                <span class="key-figure">{{ MembershipPayments::formatPrice((int) $laserCutterInvestment) }}</span>
            </p>

            <h4 class="text-center">Fees Collected</h4>
            <p class="text-center">
                <span class="key-figure">{{ MembershipPayments::formatPrice((int) $laserCutterMoneySpent) }}</span>
            </p>
        </div>
    </div>

</div>

@stop