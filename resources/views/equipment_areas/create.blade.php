@extends('layouts.main')

@section('meta-title')
    Add an area and its coordinators
@stop

@section('page-title')
    <a href="{{ route('equipment_area.index') }}">Area Coordinators</a> > Create
@stop

@section('content')
    <form action="{{ route('equipment_area.store') }}" method="POST">
        @csrf

        @include('equipment_areas/form')

        <button type="submit" class="btn btn-primary">Save</button>

    </form>
@stop
