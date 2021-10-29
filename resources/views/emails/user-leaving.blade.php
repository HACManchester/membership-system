<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Hi {{ $user['given_name'] }},<br />
    <b>😢 You're leaving Hackspace Manchester</b><br />
    Your membership has been cancelled, and you'll stop being a member of Hackspace Manchester at the end of the current payment cycle.<br />
</p>
<p>
    <b>🙁 How come you chose to leave?</b><br/>
    We would really appreciate if you could take 30 seconds to fill in our exit survey.<br/>
    If something went wrong we are keen to make it right.
    <li>Please take our exit survey: <a href="https://surveys.hacman.org.uk/index.php/735111">https://surveys.hacman.org.uk/index.php/735111</a></li>
    <li>If something went wrong, and you'd like a response, please email the board at <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a>
</p>
@if ($memberBox)
<p>
    It looks like you have a members storage box. Please ensure you have removed all your items from it otherwise they will be disposed of to free up space for other members.
</p>
@endif
<p>
    <b>You're welcome back any time!<b><br/>
    If you have changed your mind and wish to remain part of Hackspace Manchester you can do this by logging in anytime within the next 12 months and set up a subscription payment, after this time you will need to sign up again.<br />
    Alternatively if you believe this has been sent in error please email <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a> with the details of your subscription payment and we will investigate.<br />
    <a href="{{ URL::route('home') }}">Hackspace Manchester Member System</a><br/>
</p>
<p>
    Thank you,<br />
    The Hackspace Manchester board
</p>

</body>
</html>
