@extends('layouts.main')

@section('meta-title')
    Record a new piece of equipment
@stop

@section('page-title')
    Record a new piece of equipment
@stop

@section('content')

<div class="col-xs-12">

    <form action="{{ route('equipment.store') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
        @csrf

        @include('equipment/form')

        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>

    </form>

</div>

@stop
