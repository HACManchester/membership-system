@extends('layouts.main')

@section('page-title')
Tools &amp; Equipment
@stop

@section('meta-title')
Tools and Equipment
@stop

@section('main-tab-bar')

@stop

@section('page-action-buttons')
    @if (!Auth::guest() && !Auth::user()->online_only)
        <a class="btn btn-secondary" href="{{ route('equipment.create') }}">Record a new item</a>
    @endif
@stop


@section('content')

    <div class="well">
        <h3>View tools, manuals, and book inductions</h3>
        For changes to the information on the equipment pages please contact someone on
        the <a href="https://members.hacman.org.uk/groups/equipment">equipment</a> team
    </div>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Equipment requiring an induction</h3>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Access Fee</th>
                <th>Usage Cost</th>
                <th></th>
            </tr>
            </thead>
            @foreach($requiresInduction as $tool)
                <tr class="{{ $tool->isWorking() ? '': 'alert-warning'}}">
                    <td>
                        <a href="{{ route('equipment.show', $tool->slug) }}">{{ $tool->name }}</a>
                    </td>
                    <td>
                        @if (!$tool->isWorking())<span class="label label-danger">Out of action</span>@endif
                        @if ($tool->isPermaloan())<span class="label label-warning">Permaloan</span>@endif
                    </td>
                    <td>{!! $tool->requiresInduction() ? 'R' : 'O' !!}</td>
                    <td>{!! $tool->present()->accessFee() !!}</td>
                    <td>{!! $tool->present()->usageCost() !!}</td>
                    <td>
                        @if (!Auth::guest() && !Auth::user()->online_only)
                            <span class="pull-right"><a href="{{ route('equipment.edit', $tool->slug) }}" class="btn-sm">Edit</a></span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>


    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">Equipment ready to use</h3>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Usage Cost</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
        @foreach($doesntRequireInduction as $tool)
            <tr>
                <td>
                    <a href="{{ route('equipment.show', $tool->slug) }}">{{ $tool->name }}</a>
                </td>
                <td>{!! $tool->present()->usageCost() !!}</td>
                <td>
                    @if (!$tool->working)<span class="label label-danger">Out of action</span>@endif
                    @if ($tool->isPermaloan())<span class="label label-warning">Permaloan</span>@endif
                </td>
                <td>
                    @if (!Auth::guest() && Auth::user()->hasRole('equipment'))
                        <span class="pull-right"><a href="{{ route('equipment.edit', $tool->slug) }}" class="btn-sm">Edit</a></span>
                    @endif
                </td>
            </tr>
        @endforeach
        </table>
    </div>

@stop
