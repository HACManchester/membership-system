@extends('layouts.main')

@section('meta-title')
    Add a maintainer group
@stop

@section('page-title')
    <a href="{{ route('maintainer_groups.index') }}">Maintainer Groups</a> > Create
@stop

@section('content')
    <form action="{{ route('maintainer_groups.store') }}" method="POST">
        @csrf

        @include('maintainer_groups/form')

        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@stop