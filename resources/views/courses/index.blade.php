@extends('layouts.main')

@section('meta-title')
    Inductions
@stop

@section('page-title')
    Inductions
@stop

@section('page-action-buttons')
    @if (!Auth::guest() && Auth::user()->can('create', \BB\Entities\Course::class))
        <a class="btn btn-secondary" href="{{ route('courses.create') }}">Create induction</a>
    @endif
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-9 col-lg-9">
            <div class="well">
                <p>
                    Certain pieces of equipment in the Hackspace require inductions before use.
                </p>

                <p>
                    Our inductions are intended to show you how to operate the items covered safely, in line with our
                    risk assessments and workshop safety protocols.
                </p>

                <p>
                    This applies even if you are familiar with a piece of equipment from outside of the Hackspace. Both
                    for our own peace of mind, and to satisfy our insurance obligations.
                </p>

                <p>
                    Our inductions are not intended to teach or develop your skills on a particular item, but rather
                    enable you to begin using it and learning for yourself. We do separately run classes throughout the
                    year though – keep an eye on
                    <a href="https://list.hacman.org.uk/c/events/12" target="_blank">
                        the events section of our forum
                    </a>
                    if that's something you're interested in!
                </p>
            </div>
            
            @foreach ($courses as $course)
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


                    <p>
                        Completing this induction will allow you to use the following equipment:
                    </p>

                    <div class="row">
                        @foreach ($course->equipment->chunk(ceil($course->equipment->count() / 2)) as $equipmentChunk)
                            <div class="col-md-6">
                                <ul>
                                    @foreach ($equipmentChunk as $equipment)
                                        <li>
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
            @endforeach
        </div>
    </div>
@stop
