<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Hi {{ $user['given_name'] }},<br />
    <b>You're leaving Hackspace Manchester</b><br />
    Your membership has been cancelled and you'll stop being a member of Hackspace Manchester at the end of the current payment cycle.<br />
</p>
<p>
    <b>Help us improve</b><br/>

    Please do let us know if you have any thoughts or feedback for us. We really want to make the Hackspace a friendly & inclusive environment, and make it easier for new members to get involved & be productive.<br/>
    
    You can let us know via our short, anonymous, exit survey:<br/>
    <a href="https://forms.gle/5okny6T3yW3Cq8Zm6">https://forms.gle/5okny6T3yW3Cq8Zm6</a></br>

    Or by emailing us at <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a>
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
