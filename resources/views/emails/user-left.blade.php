<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Hi {{ $user['given_name'] }},<br />
    <b>😢 You've left Hackspace Manchester</b><br />
    Your last membership payment for Hackspace Manchester has now expired and you have been marked as having left.<br />
    We are sorry to see you go and hope you had a great time while you were here.<br />
    <br />
    If you have any comments or feedback we would love to hear from you, please email the board at <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a>
</p>
@if ($memberBox)
<p>
    It looks like you have a members storage box, this will be put back into circulation so please ensure you have
    removed all your items from it otherwise they will be disposed of to free up space for other members.
</p>
@endif
<p>
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
