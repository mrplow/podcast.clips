<?php
session_start();
if (! isset($_SESSION ['user_id'])) {
    header("Location: /login.php");
}
include('/var/connect.php');
$dbconnect = mysqli_connect($GLOBALS ["mysql_hostname"], $GLOBALS ["mysql_username"], $GLOBALS ["mysql_password"], $GLOBALS ["mysql_database"]);
if ($dbconnect->connect_error) {
    die("Database connection failed: " . $dbconnect->connect_error);
}
$message = '';
if ($_POST ['password'] != $_POST ['confirm_password']) {
    echo "passwords do not match";
} else {
    if (! empty($_POST ['oldpassword']) && ! empty($_POST ['password'])) {
        unset($OldPassword);
        $OldPassword = $_POST ['oldpassword'];
        $records = $dbconnect->prepare('SELECT us_rowid, us_password FROM users WHERE us_rowid = ?');
        $records->bind_param('i', $_SESSION ['user_id']);
        $records->execute();
        $records->store_result();
        $records->bind_result($UserRowid, $HashedPassword);
        while ($records->fetch()) {
            $ReturnedID = $UserRowid;
            $ReturnedPassword = $HashedPassword;
        }
        $records->close();
        $message = '';
        if (! empty($ReturnedID) && password_verify($_POST ['oldpassword'], $ReturnedPassword)) {
            $NewPassword = password_hash($_POST ['password'], PASSWORD_BCRYPT);
            $UpdatePass = $dbconnect->prepare('UPDATE users SET us_password = ?, us_mdate = NOW() WHERE us_rowid = ?');
            $UpdatePass->bind_param('si', $NewPassword, $_SESSION ['user_id']);
            $UpdatePass->execute();
            $message = 'Password updated, redirecting back home...';
            header("refresh:3; /");
        } else {
            $message = 'Sorry, those credentials do not match';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet"
	href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
	integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
	crossorigin="anonymous">
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
    <?php

                if (! empty($message)) {
                    ?>
    <p>
      <?=$message?>
    </p>
    <?php
                }
                ?>
    <div class="container">
		<div class="text-right">
        Logged in as 
        <?php echo $_SESSION['user_name']; ?>
        <br /> <a href="/logout.php">Logout </a> <br /> <a
				href="/changepass.php">Change Password </a>
		</div>
		<div class="float-right text-right">
			<a href="/">Home</a> <br /> <a href="/search.php">Search Clips</a> <br />
			<a href="/transcription.php">Search Transcriptions</a> <br />
                        <a href="/advsearch.php">Advanced Search</a>

        <?php

                                if ($_SESSION ['user_level'] <= 10) {
                                    echo "<br /><a href=\"/upload.php\">Upload episode</a>";
                                }
                                ?>
      </div>

		<h1>Change Password for 
        <?php echo $_SESSION['user_name']; ?>
      </h1>
		<br />
		<h2>Don't forget your password, there is no email reset.</h2>
		<br />
		<form action="changepass.php" method="POST">
			<div class="form-group">
				<input class="form-control" type="password"
					placeholder="old password" name="oldpassword"> <input
					class="form-control" type="password" placeholder="new password"
					name="password"> <input class="form-control" type="password"
					placeholder="confirm new password" name="confirm_password"> <input
					class="form-control" type="submit">
			</div>
		</form>
	</div>
</body>
</html>
