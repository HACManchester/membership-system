@extends('layouts.main')

@section('meta-title')
    Record a new piece of equipment
@stop

@section('page-title')
    Record a new piece of equipment
@stop

@section('content')

<div class="col-xs-12">

    {!! Form::open(array('route' => 'equipment.store', 'class'=>'form-horizontal', 'files'=>true)) !!}

    @include('equipment/form')

    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            {!! Form::submit('Save', array('class'=>'btn btn-primary')) !!}
        </div>
    </div>

    {!! Form::close() !!}

</div>

@stop
