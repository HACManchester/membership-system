@extends('layouts.main')

@section('meta-title')
Stats
@stop

@section('page-title')
Stats
@stop

@section('content')

<div class="well">
    <div class="row">
        <div class="col-sm-12">
            <h3>Monthly Finance Overview</h3>
            <p>
                This overview is of a sample month, and of the relevant season to account for heater usage. Therefore, the numbers will not be 100% accurate.
                Caveats:
                <ul>
                    <li>Some income may not materialise (e.g. chargebacks)</li>
                    <li>There may be extra income (e.g. snackspace or materials income)</li>
                    <li>There may be larger costs (e.g. quarterly payments and snackspace buys)</li>
                </ul>
                True understanding of the financial position will require the treasurer to process bank statements, and should ideally be done at AGMs.
            </p>
            <p>Ⓜ️ indicates manual hardcoded value. Dependent figures will also therefore be implied to be manual.</p>
            <p>🕖 Last updated 01/01/2022</p> 
        </div>
        @if($user->active)
            <div class="col-sm-12">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr class="bg-success">
                            <th scope="col">Incomings</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td>Expected income from membership fees</td>
                            <td>£{{ $expectedIncome }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Other income (snackspace runs, consumables etc)</td>
                            <td>£{{ $otherIncome }} Ⓜ️</td>
                        </tr>
                        <tr>
                            <td></td>
                            <th scope="row">Total incomings</th>
                            <th scope="row">£{{ $totalIncome }}</td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr class="bg-danger">
                            <th scope="col">Outgoings</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td>Rent, service charge, other landlord fees</td>
                            <td>£{{ $rent }} Ⓜ️</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Electricity</td>
                            <td>£{{ $electric }} Ⓜ️</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Other outgoings (snackspace runs, consumables etc)</td>
                            <td>£{{ $otherOutgoings }} Ⓜ️</td>
                        </tr>
                        <tr>
                            <td></td>
                            <th scope="row">Total outgoings</th>
                            <th scope="row"> £{{ $totalOutgoings }}</td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr class="bg-info">
                            <th scope="col">Difference</th>
                            <td></td>
                            <th scope="col">£{{ $totalIncome - $totalOutgoings }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @else
            <div class="alert alert-danger">
                Detailed breakdown available to active members only
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-6 col-xl-4">
        <div class="well">
            <h3 class="text-center">Payment Methods</h3>
            <p class="text-center">Paying by Direct Debit saves time and ensures your membership stays up to date.</p>

            <div id="paymentMethods" style="height:400px"></div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-xl-4">
        <div class="well">

            <h3 class="text-center">Paying Members</h3>
            <p class="text-center">
                <span class="key-figure">{{ $numMembers }}</span>
            </p>

            <h3 class="text-center">Paying at least &pound;{{ $recommendedPayment}}</h3>
            <p class="text-center">
                <span class="key-figure">
                    {{ round($numMembers / $payingRecommendedOrAbove) }}% 
                    ({{ round($payingRecommendedOrAbove) }} members)
                </span>
            </p>
            <p class="text-center">
                This is the recommended membership amount for those who can pay it.
                Paying the recommended amount helps keep the space open!
            </p>

        </div>
    </div>
</div>
<script>

    BB.chartData = BB.chartData || {};
    BB.chartData.paymentMethods = {!! json_encode($paymentMethods) !!};
    google.load("visualization", "1", {packages:["corechart"]});


    google.setOnLoadCallback(drawPaymentMethodsChart);
    function drawPaymentMethodsChart() {
        var data = google.visualization.arrayToDataTable(BB.chartData.paymentMethods);

        var options = {
            //title: 'Payment Methods',
            pieHole: 0.4
        };

        var chart = new google.visualization.PieChart(document.getElementById('paymentMethods'));
        chart.draw(data, options);
    }

</script>

@stop