<!DOCTYPE html>
<html lang="en-GB">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>New User</h2>

		<div>
			New User:<br />
            <h3> {{ $user['given_name'] }}, </h3>
            <h3> {{ $user['family_name'] }}, </h3>
            <h3> {{ $address['$line_1'] }}, </h3>
            <h3> {{ $address['$line_2'] }}, </h3>
            <h3> {{ $address['line_3'] }}, </h3>
            <h3> {{ $address['line_4'] }}, </h3>
            <h3> {{ $address['postcode'] }}, </h3>
		</div>
	</body>
</html>
