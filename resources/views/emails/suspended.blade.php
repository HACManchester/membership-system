<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Hi {{ $user['given_name'] }},<br />
    Your Hackspace Manchester membership has been suspended because of a payment problem.<br />
    <br />
    Your latest subscription payment has failed to process and we need you to login and retry your payment or make a manual payment.
</p>
<p>
    While your membership is suspended you wont have access to Hackspace Manchester.
</p>
<p>
    Please login as soon as you can and make your subscription payment.<br />
    <a href="{{ URL::route('home') }}">Hackspace Manchester Member System</a><br/>
    If you have any questions please email the <a href="mailto:board@hacman.org.uk">board</a>
</p>
<p>
    If you are leaving then we would be really grateful if you could click the leaving button in your account or let us know.
</p>
<p>
    Thank you,<br />
    The Hackspace Manchester board
</p>

</body>
</html>
