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
                <li>Trainers</li>
            </ul>
            <p>It's essential that all communication is strictly tool based, and non-personal. Emails are copied to the board.</p>
        </div>
    </div>
</div>
    
    
<div class="row">
    <div class="col-xs-12 col-md-12 col-lg-8">
        <div class="infobox well">
            <form action="{{ route('notificationemail.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-xs-12 col-md-9">
                        <div class="{{ FlashNotification::hasErrorDetail('recipient', 'has-error has-feedback') }}">
                    
                            <h3>Recipients</h3>
                            <input type="hidden" name="recipient" value="tool/{{ $equipment->slug }}/{{ $status }}">
                        
                            <label for="equipment">Equipment</label>
                            <div class="form-control">{{ $equipment->name }} 
                                (<a href="{{route('equipment.show', [$equipment->slug])}}">
                                    Visit tool
                                </a>) 
                            </div>
                            
                            <label for="status">Training Status</label>
                            <div class="form-control">{{ $statuses[$status] }}</div>
                            
                            {!! FlashNotification::getErrorDetail('recipient') !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-9">
                        <div class="{{ FlashNotification::hasErrorDetail('subject', 'has-error has-feedback') }}">
                            <h3>Subject</h3>
                            <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject') }}">
                            {!! FlashNotification::getErrorDetail('subject') !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-9">
                        <div class="form-group {{ FlashNotification::hasErrorDetail('message', 'has-error has-feedback') }}">
                            
                            <h3>Message</h3>
                            <p>Do not draft messages here, this form does not save drafts on page refreshes.</p>
                            <p>The email will be addressed to the user (e.g. Hi User), and contain the standard signature, the message above will be placed in between</p>
                            <textarea name="message" id="message" class="form-control">{{ old('message') }}</textarea>
                            {!! FlashNotification::getErrorDetail('message') !!}
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

                        <input type="checkbox" name="send_to_all" id="send_to_all" value="1" {{ old('send_to_all') ? 'checked' : '' }}>
                        <label for="send_to_all">Send the message to everyone, not just yourself</label><br>
                        <button type="submit" class="btn btn-primary">Send</button><br>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>


@stop