@extends('layouts.main')

@section('meta-title')
Login To Another Service
@stop

@section('content')


    <div class="login-container col-xs-12 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4"
        style="border-color:red">
        <div class="row">
            <div class="col-xs-12">
                <h1>⛔</h1>
                <h3>There has been an error.</h3>
                <h3>Process aborted.</h3>
                @if(isset($code))
                    <b>Error code {{ $code }}</b>
                @endif
            </div>
        </div>
    </div>

@stop