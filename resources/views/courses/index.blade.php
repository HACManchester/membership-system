@extends('layouts.main')

@section('meta-title')
    Courses
@stop

@section('page-title')
    Induction Courses
@stop

@section('page-action-buttons')
    @if (!Auth::guest() && Auth::user()->can('create', \BB\Entities\Course::class))
        <a class="btn btn-secondary" href="{{ route('courses.create') }}">Create course</a>
    @endif
@stop

@section('content')

    <div class="row">
        <div class="col-sm-12 col-md-9 col-lg-9">
            <div class="well">
                <p>
                    Some pieces of equipment within the Hackspace require inductions before you can use them.
                    Our inductions will show you how to operate the items covered, as well as making you aware of any
                    important satefy considerations or protocols we have within our workshops.
                </p>
                <p>
                    This applies even if you are familiar with a piece of equipment from outside of the Hackspace, to
                    keep our insurer's happy that you've been briefed according to our risk assessments and procedures.
                </p>
                <p>
                    Our induction courses are not intended to teach you skills or craft on a particular tool, but rather
                    get you up & running on it for your own purposes. We do separately run classes throughout the year
                    though – keep an eye on our forum if that's something you're interested in!
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

                    <ul>
                    <li><strong>Format:</strong> {{ $course->format }}</li>
                    <li><strong>Format Description:</strong> {{ $course->format_description }}</li>
                    <li><strong>Frequency:</strong> {{ $course->frequency }}</li>
                    <li><strong>Frequency Description:</strong> {{ $course->frequency_description }}</li>
                    <li><strong>Wait Time:</strong> {{ $course->wait_time }}</li>
                    </ul>
                </div>
            @endforeach

        </div>
    </div>

@stop
