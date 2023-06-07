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
    This notification email has been sent to all members who are marked as <strong>{{ $training_status }}</strong> on the {{$equipment_name}}.
</p>

<p>Message content:</p>
<p style="background-color:#eee; background:repeating-linear-gradient(45deg, #fafafa, #fafafa 40px, #fff 40px, #fff 80px); padding: 15px; border: 1px solid #fafafa;">
    {!! $messageBody !!}
</p>


<p>
    This is a notification email, replies are best directed towards <a href="https://list.hacman.org.uk">the forum</a> or Telegram. This email was sent by the <a href="{{ URL::route('home') }}">Hackspace Manchester Member System</a>, without the sender having access to your email address.<br><br>
    <em>
        To unsubscribe, log in to the membership system and cancel your training request (quickest, and preferred), or reply to this email with "unsubscribe" (there may be a delay).
    </em>
</p>

</body>
</html>
