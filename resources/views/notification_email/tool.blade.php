@extends('layouts.main')

@section('meta-title')
Email members by training status
@stop

@section('page-title')
Email members by training status
@stop

@section('content')

<div class="row">
    <div class="col-xs-12">
        <div class="well">
            <h3>Tool trainers may email people who have a training record</h3>
            <p>The categories available are:</p>
            <ul>
                <li>Awaiting training</li>
                <li>Trained</li>
                <li>Trainers/Maintainers</li>
            </ul>
            <p>It's essential that all communication is strictly tool based, and non-personal. Emails are copied to the board.</p>
        </div>
    </div>
</div>
    
    
<div class="row">
    <div class="col-xs-12 col-md-12 col-lg-8">
        <div class="infobox well">
            {!! Form::open(array('route' => 'notificationemail.store', 'class'=>'', 'method'=>'POST')) !!}

            <div class="row">
                <div class="col-xs-12 col-md-9">
                    <div class="{{ Notification::hasErrorDetail('recipient', 'has-error has-feedback') }}">
                
                        <h3>Recipients</h3>
                        {!! Form::hidden('recipient', "tool/$equipment->slug/$status", null, ['class'=>'form-control']) !!}
                       
                        {!! Form::label('equipment', 'Equipment') !!}
                        <div class="form-control">{{ $equipment->name }} 
                            (<a href="{{route('equipment.show', [$equipment->slug])}}">
                                Visit tool
                            </a>) 
                        </div>
                        
                        {!! Form::label('status', 'Training Status') !!}
                        <div class="form-control">{{ $statuses[$status] }}</div>
                         
                        {!! Notification::getErrorDetail('recipient') !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-9">
                    <div class="{{ Notification::hasErrorDetail('subject', 'has-error has-feedback') }}">
                        <h3>Subject</h3>
                        {!! Form::text('subject', null, ['class'=>'form-control']) !!}
                        {!! Notification::getErrorDetail('subject') !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-9">
                    <div class="form-group {{ Notification::hasErrorDetail('message', 'has-error has-feedback') }}">
                        
                        <h3>Message</h3>
                        <p>Do not draft messages here, this form does not save drafts on page refreshes.</p>
                        <p>The email will be addressed to the user (e.g. Hi User), and contain the standard signature, the message above will be placed in between</p>
                        {!! Form::textarea('message', null, ['class'=>'form-control']) !!}
                        {!! Notification::getErrorDetail('message') !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <p>
                        <b>Important!</b><br>
                        <ul>
                            <li>The message will be sent as soon as you click send.</li>
                            <li>If you do not tick the checkbox, it will only go to you</li>
                            <li>Be sure to copy the message if you're writing a draft, as the message won't reappear once the page loads</li>
                            </ul>
                    </p>
                    <p>To actually send the email to everyone, make sure to tick the checkbox below.</p>

                        {!! Form::checkbox('send_to_all') !!}
                        {!! Form::label('send_to_all', 'Send the message to everyone, not just yourself') !!}<br>
                    {!! Form::submit('Send', array('class'=>'btn btn-primary')) !!}<br>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>


@stop