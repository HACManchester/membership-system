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
                        <form action="{{ route('register') }}" method="GET" class="form-horizontal">
                            <input type="hidden" name="gift_certificate" value="1">
                            <input type="text" name="gift_code" class="form-control" required="required" placeholder="XXX-YYY-ZZZ" value="{{ old('gift_code') }}">
                            <button type="submit" class="btn btn-primary">Claim your gift!</button>
                        </form>
                    </p>
                </div>

            </div>
        </div>
	</div>
@stop
