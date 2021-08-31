<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>Confirm your email address</h1>
<p>Hi {{ $user['given_name'] }},</p>

<p>Please click the link below to confirm your email address:<br /><br /> <a href="{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}">{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}</a></p>

<hr/>
<p>Many Thanks</p>
<p>Hackspace Manchester Board</p>
</body>
</html>
