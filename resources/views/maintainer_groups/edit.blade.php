@extends('layouts.main')

@section('meta-title')
    Edit a maintainer group
@stop

@section('page-title')
    <a href="{{ route('maintainer_groups.index') }}">Maintainer Groups</a> >
    <a href="{{ route('maintainer_groups.show', $maintainerGroup->slug) }}">{{ $maintainerGroup->name }}</a> >
    Edit
@stop

@section('content')
    <form action="{{ route('maintainer_groups.update', $maintainerGroup) }}" method="POST">
        @csrf
        @method('PUT')

        @include('maintainer_groups/form')

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@stop