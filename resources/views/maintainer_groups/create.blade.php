@extends('layouts.main')

@section('meta-title')
    Add a maintainer group
@stop

@section('page-title')
    <a href="{{ route('maintainer_groups.index') }}">Maintainer Groups</a> > Create
@stop

@section('content')
    {!! Form::open(['route' => 'maintainer_groups.store']) !!}

    @include('maintainer_groups/form')

    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}
@stop
