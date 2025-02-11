@extends('layouts.main')

@section('meta-title')
    Edit an induction
@stop

@section('page-title')
    <a href="{{ route('courses.index') }}">Courses</a> >
    <a href="{{ route('courses.show', $course->slug) }}">{{ $course->name }}</a> >
    Edit
@stop

@section('content')
    {!! Form::model($course, [
        'route' => ['courses.update', $course],
        'method' => 'PUT',
    ]) !!}

    @include('courses/form')

    {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@stop
