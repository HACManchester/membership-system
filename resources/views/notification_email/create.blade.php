@extends('layouts.main')

@section('meta-title')
Email all active members
@stop

@section('page-title')
Email Members
@stop

@section('content')

    <div class="row page-header">
        <div class="col-xs-12">
            <p>
                Send an email to all the active members or specific groups.<br />
                If you manage a group you will only have permission to send to that group, only admins can email everyone.
            </p>
        </div>
    </div>

    <div class="col-xs-12 col-md-12 col-lg-8">
        <form action="{{ route('notificationemail.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group {{ FlashNotification::hasErrorDetail('recipient', 'has-error has-feedback') }}">
                        <label for="recipient">Recipient</label>
                        <select name="recipient" id="recipient" class="form-control">
                            @foreach($recipients as $value => $label)
                                <option value="{{ $value }}" {{ old('recipient') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        {!! FlashNotification::getErrorDetail('recipient') !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group {{ FlashNotification::hasErrorDetail('subject', 'has-error has-feedback') }}">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject') }}">
                        {!! FlashNotification::getErrorDetail('subject') !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-8">
                    <div class="form-group {{ FlashNotification::hasErrorDetail('message', 'has-error has-feedback') }}">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control">{{ old('message') }}</textarea>
                        {!! FlashNotification::getErrorDetail('message') !!}
                        <p>The email will be addressed to the user and contain the standard signature, the message above will be placed inbetween</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary">Send</button>
                    <input type="checkbox" name="send_to_all" id="send_to_all" value="1" {{ old('send_to_all') ? 'checked' : '' }}>
                    <label for="send_to_all">Send the message to everyone, not just yourself</label>
                    <p>Make sure everything is alright as the message will be sent as soon as you click send, if your just
                    testing make sure you have the message elsewhere as it wont be here when the page loads</p>
                </div>
            </div>

        </form>
    </div>


@stop