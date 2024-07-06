@extends('layouts.main')

@section('meta-title')
    Area
@stop

@section('page-title')
    Area Coordinators
@stop

@section('page-action-buttons')
    @if (!Auth::guest() && Auth::user()->can('create', \BB\Entities\EquipmentArea::class))
        <a class="btn btn-secondary" href="{{ route('equipment_area.create') }}">Create area</a>
    @endif
@stop

@section('content')

    <div class="row">
        <div class="col-sm-12 col-md-9 col-lg-9">
            <div class="well">
                <p>
                    At Hackspace Manchester we have a number of voluntary members that are responsible for the various areas
                    within the hackspace, these are our Area Coordinators, and they will:
                </p>

                <ul>
                    <li>Help members get involved in their area (making, maintenance, training, upgrading, etc)</li>
                    <li>Being the member’s point of contact for an area</li>
                    <li>Coordinating decisions within that area</li>
                    <li>Communicating activities of the area to directors and membership</li>
                    <li>Helping organise area meetings and activities when needed</li>
                </ul>

                <p>
                    For full details on the area coordinators system, please see the forum post
                    <a href="https://list.hacman.org.uk/t/replacement-of-subgroups-with-area-coordinators/3851"
                        target="_blank">Replacement of 'subgroups' with 'area coordinators'</a>.
                </p>
            </div>
            @foreach ($areas as $area)
                <div class="well">
                    <a href="{{ route('equipment_area.show', $area) }}" class="">
                        <h4 class="list-group-item-heading">{{ $area->name }}</h4>
                    </a>
                    
                    <p class="list-group-item-text">
                        {{ $area->description }}
                    </p>
                    
                    <h5>Area Coordinators</h5>
                    <ul class="list-group">
                        @foreach ($area->areaCoordinators as $coordinator)
                            <li class="list-group-item">
                                <a href="{{ route('members.show', $coordinator->id) }}">
                                    {!! HTML::memberPhoto($coordinator->profile, $coordinator->hash, 64, 'hidden-sm hidden-xs') !!}
                                    <span>{{ $coordinator->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

        </div>
    </div>

@stop
