@extends('layouts.main')

@section('meta-title')
    Add an area and its coordinators
@stop

@section('page-title')
    <a href="{{ route('equipment_area.index') }}">Area Coordinators</a> > Create
@stop

@section('content')
    {!! Form::open(['route' => 'equipment_area.store']) !!}

    @include('equipment_areas/form')

    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@stop
