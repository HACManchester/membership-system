<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h3>
    Hi {{ $user->given_name }},
</h3>

<p style="border-left: 3px solid blue; padding: 10px;">
    This email has been sent to all members who are marked as <strong>{{ $training_status }}</strong> on the {{$equipment_name}}.
</p>

<p>Message content:</p>
<p style="background-color:#eee; background:repeating-linear-gradient(45deg, #fafafa, #fafafa 40px, #fff 40px, #fff 80px); padding: 15px; border: 1px solid #fafafa;">
    {!! $messageBody !!}
</p>


<p>
    This email was sent by the <a href="{{ URL::route('home') }}">Hackspace Manchester Member System</a>.<br><br>
    <em>
        Note: the sender may not have access to email addresses. If you wish to unsubscribe, either reply to the board asking to be removed from the training list, or log in and cancel your training request.
    </em>
</p>

</body>
</html>
