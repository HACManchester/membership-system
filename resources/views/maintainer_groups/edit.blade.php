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
    {!! Form::model($maintainerGroup, [
        'route' => ['maintainer_groups.update', $maintainerGroup],
        'method' => 'PUT',
    ]) !!}

    @include('maintainer_groups/form')

    {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@stop
