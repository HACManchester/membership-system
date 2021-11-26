@extends('layouts.main')

@section('content')
	<div class="col-sm-12 col-md-8 col-lg-8 col-md-offset-2 col-lg-offset-2">
        <div class="panel panel-default" style="opacity:0.95;box-shadow:0 0 40px white;">
            <div class="panel-body">
                <h1>Hackspace Manchester</h1>
                
                <div class="alert alert-success">
                    <p>
                        <h3>A gift for you awaits!</h3>
                        <p>Enter your gift code below, and you'll be able to register your account</p>
                        {!! Form::open(array('route' => 'register', 'class'=>'form-horizontal')) !!}
                        {!! Form::hidden('online_only', '1') !!}
                        {!! Form::text('gift_code', null, ['class'=>'form-control', 'autocomplete'=>'display-name', 'required' => 'required']) !!}
                        {!! Form::submit('Claim your gift!', array('class'=>'btn btn-primary')) !!}
                        {!! Form::close() !!}
                    </p>
                </div>

            </div>
        </div>
	</div>
@stop
