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
            @if($user && !$user->induction_completed)
                <h3>You need to have a general induction before you can get access to the space</h3>
                <p>To get an general induction:</p>
                
                <ol>
                    <li>Visit the space on open evening (or arrange to visit a member on Telegram)</li>
                    <li>Ask for a general induction</li>
                    <li>Have the induction marked as completed:
                        <ul>
                            <li>by entering the induction completion code into the form below,</li>
                            <li> or giving your email to the person who completes the induction.</li>
                        </ul>
                    </ul>
                </ol>
            @else
                <h2>You can give the general induction to others!</h2>
                <p>Follow the induction crib sheet by the entrance, it includes
                    <ul>
                        <li>giving a tour of the space</li>
                        <li>sharing important information on how the space works</li>
                    </ul>    
                </p>
                <p>The crib sheets lives in the orange tray by the first aid kit by the main door.</p>
                <h3>The general induction completion code</h3>
                <p>The completion code may be issued after a tour, and can be found at the bottom of the sheet, or below.</p>
                <p>Please only give this code to new members <strong>after</strong> giving them the full general induction as specified by the induction sheet.
                <div class="infobox__code">{!! $general_induction_code !!}</div>
            @endif
        </div>

        <div class="well">
            <h2>How general inductions work</h2>
            <ul>
                <li>All new members are given a general induction before they have access to the space.</li>
                <li>A general induction consists of a tour of the space followed by important information on how we operate.</li>
                <li>Any active member who has done the general induction can give a general induction, following the crib sheet by the main doors.</li>
            </ul>
            
            <h3>Getting marked as having completed the induction</h3>
            <p>
                Once your induction is complete, you will need to be marked as inducted on the membership system.
            </p>
                This can be done by filling in the form below, either by the person doing the induction:
                    <ul>
                        <li>passing you the induction code, for you to fill in</li>
                        <li>entering your email address into this form on their account</li>
                    </ul>
                </li>
            </p>
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
