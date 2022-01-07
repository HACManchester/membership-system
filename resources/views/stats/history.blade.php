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
            <h4>Membership History</h4>
            <div id="membershipHistory" style="height:400px"></div>
        </div>
    </div>
</div>

<script>

    BB.chartData = BB.chartData || {};
    BB.chartData.historyData = {!! json_encode($historyData) !!};
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawMembershipHistoryChart);

    function drawMembershipHistoryChart() {
        var data = google.visualization.arrayToDataTable(BB.chartData.historyData);

        var options = {
            title: 'Membership History',
            series: {
                // Gives each series an axis name that matches the Y-axis below.
                0: {targetAxisIndex: 0, pointSize: 5},
                1: {targetAxisIndex: 1, type: 'line', color: '#5a0'},
                2: {targetAxisIndex: 1, type: 'line', color: '#f00'}
            },
            seriesType: 'area',
            pointSize: 10,
            vAxes: {
                // Adds labels to each axis; they don't have to match the axis names.
                0: {title: "Total Members", maxValue: 200, minValue: 100},
                1: {title: "Joins & Leaves", gridlines: { interval: 1 }}
            }

        };

        var chart = new google.visualization.ComboChart(document.getElementById('membershipHistory'));
        chart.draw(data, options);
    }

</script>

@stop