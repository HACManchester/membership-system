@extends('layouts.main')

@section('meta-title')
    {{ $course->name }}
@stop

@section('page-title')
    <a href="{{ route('courses.index') }}">Inductions</a> > {{ $course->name }}
@stop

@section('page-action-buttons')
    @can('update', $course)
        <a class="btn btn-secondary" href="{{ route('courses.edit', $course) }}">Edit</a>
    @endcan
    @can('delete', $course)
        <button class="btn btn-danger" data-toggle="modal" data-target="#course-deletion-modal">Delete</button>
    @endcan
@stop

@section('content')
    <div class="well">
        <a href="{{ route('courses.show', $course) }}" class="">
            <h4 class="list-group-item-heading">{{ $course->name }}</h4>
        </a>

        <p class="list-group-item-text">
            {{ $course->description }}
        </p>
        <div class="row text-center" style="margin-top: 1em;">
            <div class="col-xs-4">
                <h5 class="text-muted">Format</h5>
                <h4>{{ $course->present()->format }}</h4>
                <p>{{ $course->format_description }}</p>
            </div>
            <div class="col-xs-4">
                <h5 class="text-muted">Frequency</h5>
                <h4>{{ $course->present()->frequency }}</h4>
                <p>{{ $course->frequency_description }}</p>
            </div>
            <div class="col-xs-4">
                <h5 class="text-muted">Wait Time</h5>
                <h4>{{ $course->wait_time }}</h4>
            </div>
        </div>
    </div>

    <div class="well">
        <h3>Equipment</h3>

        <p>
            Completing this induction will allow you to use the following equipment:
        </p>

        <div class="row">
            @foreach ($course->equipment->chunk(ceil($course->equipment->count() / 2)) as $equipmentChunk)
                <div class="col-md-6">
                    <ul class="list-group">
                        @foreach ($equipmentChunk as $equipment)
                            <li class="list-group-item">
                                <a href="{{ route('equipment.show', $equipment) }}">
                                    <span>{{ $equipment->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    @can('delete', $course)
        <div class="modal fade" tabindex="-1" role="dialog" id="course-deletion-modal">
            <form class="modal-dialog" role="document" action="{{ route('courses.destroy', $course) }}" method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirm deletion</h4>
                    </div>
                    <div class="modal-body">
                        <p>Deleting <em>{{ $course->name }}</em> will remove it from the members system entirely.</p>
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
