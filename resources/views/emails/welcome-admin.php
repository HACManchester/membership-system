<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>New User Alert</h1>
<h3> {{ $user['given_name'] }} </h3>
<h3> {{ $user['family_name'] }} </h3>
<h3> {{ $address['$line_1'] }} </h3>
<h3> {{ $address['$line_2'] }} </h3>
<h3> {{ $address['line_3'] }} </h3>
<h3> {{ $address['line_4'] }} </h3>
<h3> {{ $address['postcode'] }} </h3>
</body>
</html>
