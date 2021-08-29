<!DOCTYPE html>
<html lang="en-GB">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>New User</h2>

		<div>
			New User:<br />
    
           <h3> <?php echo  $user['given_name']; ?> </h3>
           <h3> <?php echo  $user['family_name']; ?></h3>
           <h3> <?php echo  $user['address']['line_1'] ?> </h3>
           <h3> <?php echo  $user['address']['line_2'] ?></h3>
           <h3> <?php echo  $user['address']['line_3'] ?> </h3>
           <h3> <?php echo  $user['address']['line_4'] ?></h3>
           <h3> <?php echo  $user['address']['postcode'] ?> </h3>
           <h3> <?php echo  $address['line_1'] ?> </h3>
           <h3> <?php echo  $address['line_2'] ?></h3>
           <h3> <?php echo  $address['line_3'] ?> </h3>
           <h3> <?php echo  $address['line_4'] ?></h3>
           <h3> <?php echo  $address['postcode'] ?> </h3>

		</div>
	</body>
</html>
