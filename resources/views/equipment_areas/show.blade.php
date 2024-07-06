@extends('layouts.main')

@section('meta-title')
    {{ $equipmentArea->name }}
@stop

@section('page-title')
    <a href="{{ route('equipment_area.index') }}">Area Coordinators</a> > {{ $equipmentArea->name }}
@stop

@section('page-action-buttons')
    @can('update', $equipmentArea)
        <a class="btn btn-secondary" href="{{ route('equipment_area.edit', $equipmentArea) }}">Edit</a>
    @endcan
    @can('delete', $equipmentArea)
        <button class="btn btn-danger" data-toggle="modal" data-target="#equipment-area-deletion-modal">Delete</button>
    @endcan
@stop

@section('content')
    <div class="well">
        <h3>Description</h3>
        {{ $equipmentArea->description }}
    </div>

    <div class="well">
        <h3>Area Coordinators</h3>
        <ul class="list-group">
            @foreach ($equipmentArea->areaCoordinators as $coordinator)
                <li class="list-group-item">
                    <a href="{{ route('members.show', $coordinator->id) }}">
                        {!! HTML::memberPhoto($coordinator->profile, $coordinator->hash, 64) !!}
                        <span>{{ $coordinator->name }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    @can('delete', $equipmentArea)
        <div class="modal fade" tabindex="-1" role="dialog" id="equipment-area-deletion-modal">
            <form class="modal-dialog" role="document" action="{{ route('equipment_area.destroy', $equipmentArea) }}"
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
                        <p>Deleting <em>{{ $equipmentArea->name }}</em> will remove it from the members system entirely.</p>
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
