@extends('layouts.main')

@section('meta-title')
    Add an induction
@stop

@section('page-title')
    <a href="{{ route('courses.index') }}">Courses</a> > Create
@stop

@section('content')
    {!! Form::open(['route' => 'courses.store']) !!}

    @include('courses/form')

    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@stop
