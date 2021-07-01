<?php
session_start ();
if (isset ( $_SESSION ['user_id'] )) {
	header ( "Location: /" );
}
include ('/var/connect.php');
$dbconnect = mysqli_connect ( $GLOBALS ["mysql_hostname"], $GLOBALS ["mysql_username"], $GLOBALS ["mysql_password"], $GLOBALS ["mysql_database"] );
if ($dbconnect->connect_error) {
	die ( "Database connection failed: " . $dbconnect->connect_error );
}
unset ( $OwnerRows );
$CheckOwner = $dbconnect->prepare ( 'SELECT * FROM users WHERE us_rowid_userlevel = 1' );
$CheckOwner->execute ();
$CheckOwner->store_result ();
$OwnerRows = $CheckOwner->num_rows;
$message = '';
if ($_POST ['password'] != $_POST ['confirm_password']) {
	echo "passwords do not match";
} else {
	if (! empty ( $_POST ['username'] ) && ! empty ( $_POST ['password'] )) {
		unset ( $Username, $Password );
		$Username = $_POST ['username'];
		$Password = password_hash ( $_POST ['password'], PASSWORD_BCRYPT );
		if ($OwnerRows > 0) {
			$NewUser = $dbconnect->prepare ( 'INSERT INTO users (us_rowid_userlevel, us_username, us_password, us_cdate) VALUES (3, ?, ?, NOW())' );
		} else {
			$NewUser = $dbconnect->prepare ( 'INSERT INTO users (us_rowid_userlevel, us_username, us_password, us_cdate) VALUES (1, ?, ?, NOW())' );
		}
		$NewUser->bind_param ( 'ss', $Username, $Password );
		if ($NewUser->execute ()) {
			$message = 'Successfully created new user';
		} else {
			$message = 'Sorry there was an issue creating your account, maybe that username is already registered';
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>The After Disaster Podcast Clips</title>
<link rel="stylesheet"
	href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
	integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
	crossorigin="anonymous">
<meta name="viewport"
	content="width=device-width, initial-scale=1, shrink-to-fit=no">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
	integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
	crossorigin="anonymous">
  </script>
<script
	src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
	integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
	crossorigin="anonymous">
  </script>
</head>
<body>
	<div class="container">
    <?php

				if (! empty ( $message )) {
					?>
    <p>
      <?=$message?>
    </p>
    <?php
				}
				?>
    <h1>Register</h1>
		<span>or <a href="login.php">login here </a>
		</span>
    <?php
				if ($OwnerRows > 0) {
					echo "<br /><h2>Don't forget your password, there is no email reset.</h2><br />";
				} else {
					echo "<br /><h2>This is the first login, create new owner user.</h2><br />";
				}
				?>
    <form action="register.php" method="POST">
			<input type="text" placeholder="Enter your username" name="username">
			<input type="password" placeholder="and password" name="password"> <input
				type="password" placeholder="confirm password"
				name="confirm_password"> <input type="submit">
		</form>
	</div>
</body>
</html>
