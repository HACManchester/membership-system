@extends('layouts.main')

@section('meta-title')
    Edit an area & its coordinators
@stop

@section('page-title')
    <a href="{{ route('equipment_area.index') }}">Area Coordinators</a> >
    <a href="{{ route('equipment_area.show', $equipmentArea->slug) }}">{{ $equipmentArea->name }}</a> >
    Edit
@stop

@section('content')
    {!! Form::model($equipmentArea, [
        'route' => ['equipment_area.update', $equipmentArea],
        'method' => 'PUT',
    ]) !!}

    @include('equipment_areas/form')

    {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@stop
