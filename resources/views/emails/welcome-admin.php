<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>{{ $user['given_name'] }} {{ $user['family_name'] }}</p>
<p>{{ $user['address']['line_1'] }}</p>
<p>{{ $user['address']['line_2'] }}</p>
<p>{{ $user['address']['line_3'] }}</p>
<p>{{ $user['address']['postcode'] }}</p>
</body>
</html>
