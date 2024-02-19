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
    @can('create', \BB\Entities\Equipment::class)
        <a class="btn btn-secondary" href="{{ route('equipment.create') }}">Record a new item</a>
    @endcan
@stop

@section('content')
    <div class="well">
        <h3>View tools, manuals, and book inductions</h3>
        For changes to the information on the equipment pages please contact someone on
        the <a href="{{ route('groups.show', 'equipment' ) }}">equipment</a> team
    </div>

    @foreach($equipmentByRoom as $k => $tools)
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Tools in {{ ucfirst(str_replace('-', ' ', $k)) }}</h3>
        </div>
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Bloody Dangerous</th>
                <th></th>
            </tr>
            </thead>
            @foreach($tools as ['equipment' => $tool, 'trained' => $userIsTrained])
                <tr>
                    <td>
                        <a href="{{ route('equipment.show', $tool->slug) }}">{{ $tool->name }}</a>
                    </td>
                    <td>
                        @if (!!$tool->requires_induction)<span class="label label-info">Induction Required</span>@endif
                        @if (!!$tool->requires_induction && !$tool->accepting_inductions)<span class="label label-warning">Inductions paused</span>@endif
                        @if (!$tool->isWorking())<span class="label label-danger">Out of action</span>@endif
                        @if ($tool->isPermaloan())<span class="label label-warning">Permaloan</span>@endif
                        @if ($tool->access_code)
                            @if ($userIsTrained)
                                <span class="label label-info">🔑 Access Code: <span>{{ $tool->access_code}}</span></span>
                            @endif
                        @endif
                    </td>
                    <td>{!! $tool->isDangerous() ? '⚠️' : '' !!}</td>
                    <td>
                        @can('update', $tool)
                            <span class="pull-right"><a href="{{ route('equipment.edit', $tool->slug) }}" class="btn-sm">Edit</a></span>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    @endforeach
@stop
