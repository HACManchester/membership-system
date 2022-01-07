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
                0: {axis: 'Temps'},
                1: {axis: 'Daylight'}
            },
            axes: {
                // Adds labels to each axis; they don't have to match the axis names.
                y: {
                    Temps: {label: 'Temps (Celsius)'},
                    Daylight: {label: 'Daylight'}
                }
            }

        };

        var chart = new google.visualization.LineChart(document.getElementById('membershipHistory'));
        chart.draw(data, options);
    }

</script>

@stop