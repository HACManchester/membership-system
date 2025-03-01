@extends('layouts.main')

@section('meta-title')
    Maintainer Groups
@stop

@section('page-title')
    Maintainer Groups
@stop

@section('page-action-buttons')
    @if (!Auth::guest() && Auth::user()->can('create', \BB\Entities\MaintainerGroup::class))
        <a class="btn btn-secondary" href="{{ route('maintainer_groups.create') }}">Create maintainer group</a>
    @endif
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-9 col-lg-9">
            <div class="well">
                <p>Our maintainer groups are responsible for looking after various pieces of equipment at the Hackspace.</p>
                <p>If you want to get involved and help look after our equipment, please speak to the relevant area coordinators.</p>
            </div>
            @foreach ($maintainerGroups as $maintainerGroup)
                <div class="well">
                    <a href="{{ route('maintainer_groups.show', $maintainerGroup) }}">
                        <h4 class="list-group-item-heading">{{ $maintainerGroup->name }}</h4>
                    </a>
                    
                    @if ($maintainerGroup->equipmentArea)
                        <a href="{{ route('equipment_area.show', $maintainerGroup->equipmentArea) }}">
                            <span class="label label-default">{{ $maintainerGroup->equipmentArea->name }}</span>
                        </a>
                    @endif
                    
                    <p class="list-group-item-text">
                        {{ $maintainerGroup->description }}
                    </p>
                    

                    <h5>Maintainers</h5>
                    <div class="row">
                        @foreach ($maintainerGroup->maintainers->chunk(ceil($maintainerGroup->maintainers->count() / 2)) as $maintainerChunk)
                            <div class="col-md-6">
                                <ul class="list-group">
                                    @foreach ($maintainerChunk as $maintainer)
                                        <li class="list-group-item">
                                            <a href="{{ route('members.show', $maintainer->id) }}">
                                                @include('partials.components.member-photo', [
                                                    'profileData' => $maintainer->profile,
                                                    'userHash' => $maintainer->hash,
                                                    'size' => 48,
                                                    'class' => 'hidden-sm hidden-xs'
                                                ])
                                                <span>{{ $maintainer->name }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>

@stop
