<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>Hi {{ $user['given_name'] }},</p>
<p><b>You've left Hackspace Manchester</b></p>
<p>Your last membership payment for Hackspace Manchester has now expired and you have been marked as having left.</p>
<p><b>Help us improve</b></p>
<p>Please do let us know if you have any thoughts or feedback for us. We really want to make the Hackspace a friendly & inclusive environment, and make it easier for new members to get involved & be productive.</p>
<p>
    You can let us know via our short, anonymous, exit survey:
    <a href="https://forms.gle/5okny6T3yW3Cq8Zm6">https://forms.gle/5okny6T3yW3Cq8Zm6</a>
</p>
<p>
    Or by emailing us at
    <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a>
</p>
@if ($memberBox)
<p>
    It looks like you have a members storage box, this will be put back into circulation so please ensure you have
    removed all your items from it otherwise they will be disposed of to free up space for other members.
</p>
@endif
<p>If you have changed your mind and wish to remain part of Hackspace Manchester you can do this by logging in anytime within the next 12 months and set up a subscription payment, after this time you will need to sign up again.</p>
<p>Alternatively if you believe this has been sent in error please email <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a> with the details of your subscription payment and we will investigate.</p>
<p><a href="{{ URL::route('home') }}">Hackspace Manchester Member System</a></p>
<p>
    Thank you,<br />
    The Hackspace Manchester board
</p>
</body>
</html>
