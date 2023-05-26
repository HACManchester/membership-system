@extends('layouts.main')

@section('meta-title')
    Complete a member's general induction
@stop

@section('page-title')
    Hackspace general induction and tour
@stop

@section('content')
    <style>
        .well img {
            max-height: 200px;
            width: 100%;
            object-fit: cover;
        }
    </style>

    <div class="col-sm-12 col-lg-8 col-sm-offset-2">
        <div class="well infobox">
            <h2>How general inductions work</h2>
            <ul>
                <li>General inductions are given in the space by members, following the tour sheet by the doors</li>
                <li>Once the induction is complete, the new member must be marked as inducted on the membership system (below):
                    <ul>
                        <li>by passing the induction code to the new member, for them to fill in</li>
                        <li>by entering the code yourself, alongside the new member's email</li>
                    </ul>
                </li>
                <li>Anyone can mark another member as inducted using their email address</li>
            </ul>

            @if($user && !$user->induction_completed)
                <h2>You need to have a general induction before you can get access to the space</h2>
                <p>The general induction is given on open evenings and includes a tour of the space.</p>
                
                <ol>
                    <li>Visit the space on open evening</li>
                    <li>Ask for a general induction</li>
                    <li>Mark yourself as inducted by entering the induction code, or give your email to the person who completes the induction.</li>
                </ol>
            @else
                <h2>You've had the general induction, and can now induct others!</h2>
                <p>Follow the induction sheet by the entrance to give inductions to new members.</p>
                <h3>The general induction code</h3>
                <p>Please only give this code to new members <strong>after</strong> giving them the full general induction as specified by the induction sheet.
                <div class="infobox__code">{!! $general_induction_code !!}</div>
            @endif
        </div>
            
            
        <div class="well">
            <h2>Complete a general induction</h2>


            {!! Form::open(array('route' => 'general-induction.update', 'class'=>'form-horizontal', 'method'=>'PUT')) !!}
                <div class="form-group {{ Notification::hasErrorDetail('induction_code', 'has-error has-feedback') }}">
                    <div class="col-sm-12 col-lg-8 col-sm-offset-2">
                        <h3>Enter the code for the general induction</h3>
                        {!! Form::text('induction_code', $prefill_induction_code, ["class"=>"form-control", "placeholder" => "XXXX", 'required' => 'required']) !!}
                        {!! Form::label('induction_code', 'Enter the induction code') !!}
                        {!! Notification::getErrorDetail('induction_code') !!}
                    </div>
                </div>
                <div class="form-group {{ Notification::hasErrorDetail('inductee_email', 'has-error has-feedback') }}">
                    <div class="col-sm-12 col-lg-8 col-sm-offset-2">
                        <h3>Who should now be marked as inducted?</h3>
                        {!! Form::text('inductee_email', $user ? $user->email : "", ["class"=>"form-control", 'required' => 'required']) !!}
                        {!! Form::label('inductee_email', 'Email address of the person who is to be marked as inducted.') !!}
                        {!! Notification::getErrorDetail('inductee_email') !!}
                    </div>
                </div>
                <div class="form-group {{ Notification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
                    <div class="col-sm-12 col-lg-8 col-sm-offset-2">
                        {!! Form::checkbox('rules_agreed', true, null, ['class'=>'', 'required' => 'required']) !!}
                        {!! Form::label('rules_agreed', 'The member has been given the full induction') !!}
                        {!! Notification::getErrorDetail('rules_agreed') !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-lg-8 col-sm-offset-2">
                        {!! Form::submit('Mark user as inducted', array('class'=>'btn btn-primary')) !!}
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
        
    </div>
@stop
