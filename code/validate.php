<?php
session_start();
if (! isset($_SESSION ['user_id'])) {
    header("Location: /login.php");
}

if (isset($_SESSION ['user_validated'])) {
    $LastValidated = new DateTime($_SESSION ['user_validated']);
    $CurrentTime = new DateTime('now');
    $SinceValidated = $LastValidated->diff($CurrentTime);
    $DaysSince = $SinceValidated->format('%a');
    if ($DaysSince <= 32) {
        header("Location: /");
    }
}
include('/var/connect.php');
$dbconnect = mysqli_connect($GLOBALS ["mysql_hostname"], $GLOBALS ["mysql_username"], $GLOBALS ["mysql_password"], $GLOBALS ["mysql_database"]);
if ($dbconnect->connect_error) {
    die("Database connection failed: " . $dbconnect->connect_error);
}
unset($message);
if (! empty($_POST ['secretcode'])) {
    include('secret.php');
    unset($SecretCode, $fail);
    $SecretCode = $_POST ['secretcode'];
    if ($SecretCode !== $CurrentCode) {
        $message = "<div class=\"alert alert-danger\" role=\"alert\">Sorry, wrong code</div>";
    } else {
        $Validated = $dbconnect->prepare('UPDATE users SET us_validated = NOW() WHERE us_rowid = ?');
        $Validated->bind_param('i', $_SESSION ['user_id']);
        $Validated->execute();
        $message = "<div class=\"alert alert-success\" role=\"alert\">Nailed it! Loading main page...</div>";
        header("refresh:3; /");
        $_SESSION ['user_validated'] = date('Y-m-d H:i:s');
    }
}
?>
<!DOCTYPE html>
<html>
<body>


<head>
<meta charset="UTF-8">
<title>The After Disaster Podcast Clips</title>
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
<div class="container">
	<h1>What is this month's secret code?</h1>
	<form action="validate.php" method="POST">
		<div class="form-group">
			<input class="form-control" type="text"
				placeholder="Whats the secret code" name="secretcode"> <input
				class="form-control" type="submit">
		</div>
	</form>
<?php
if (! empty($message)) {
    echo $message;
}
?>
    <h4>
		Get the code from <a href="https://www.patreon.com/posts/49728941"
			target="_blank">Patreon</a>
	</h4>
</div>
</body>
</html>
