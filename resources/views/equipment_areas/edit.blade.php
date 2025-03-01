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
    <form action="{{ route('equipment_area.update', $equipmentArea) }}" method="POST">
        @csrf
        @method('PUT')

        @include('equipment_areas/form')

        <button type="submit" class="btn btn-primary">Update</button>

    </form>
@stop
