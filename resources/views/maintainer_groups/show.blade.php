@extends('layouts.main')

@section('meta-title')
    {{ $maintainerGroup->name }}
@stop

@section('page-title')
    <a href="{{ route('maintainer_groups.index') }}">Maintainer Groups</a> > {{ $maintainerGroup->name }}
@stop

@section('page-action-buttons')
    @can('update', $maintainerGroup)
        <a class="btn btn-secondary" href="{{ route('maintainer_groups.edit', $maintainerGroup) }}">Edit</a>
    @endcan
    @can('delete', $maintainerGroup)
        <button class="btn btn-danger" data-toggle="modal" data-target="#equipment-area-deletion-modal">Delete</button>
    @endcan
@stop

@section('content')
    <div class="well">
        @if ($maintainerGroup->equipmentArea)
            <h3>Area</h3>
            <a href="{{ route('equipment_area.show', $maintainerGroup->equipmentArea) }}">
                {{ $maintainerGroup->equipmentArea->name }}
            </a>
        @endif

        <h3>Description</h3>
        {{ $maintainerGroup->description }}
    </div>

    <div class="well">
        <h3>Maintainers</h3>
        <ul class="list-group">
            @foreach ($maintainerGroup->maintainers as $maintainer)
                <li class="list-group-item">
                    <a href="{{ route('members.show', $maintainer->id) }}">
                        {!! HTML::memberPhoto($maintainer->profile, $maintainer->hash, 64) !!}
                        <span>{{ $maintainer->name }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="well">
        <h3>Equipment</h3>
        <ul class="list-group">
            @foreach ($maintainerGroup->equipment as $equipment)
                <li class="list-group-item">
                    <a href="{{ route('equipment.show', $equipment) }}">
                        <span>{{ $equipment->name }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    @can('delete', $maintainerGroup)
        <div class="modal fade" tabindex="-1" role="dialog" id="equipment-area-deletion-modal">
            <form class="modal-dialog" role="document" action="{{ route('maintainer_groups.destroy', $maintainerGroup) }}"
                method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirm deletion</h4>
                    </div>
                    <div class="modal-body">
                        <p>Deleting <em>{{ $maintainerGroup->name }}</em> will remove it from the members system entirely.</p>
                        <p>Are you sure you want to delete this item?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    @endcan
@stop
