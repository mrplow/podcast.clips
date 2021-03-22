<?php
session_start();

#if (isset($_SESSION['user_id']))
#{
#    header("Location: /index2.php");
#}
include ('/var/connect.php');

$dbconnect = mysqli_connect($GLOBALS["mysql_hostname"], $GLOBALS["mysql_username"], $GLOBALS["mysql_password"], $GLOBALS["mysql_database"]);

if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}

if (isset($_SESSION['user_id']))
{

    $records = $dbconnect->prepare('SELECT us_rowid, us_username FROM users WHERE us_rowid = ?');
    $records->bind_param('i', $_SESSION['user_id']);
    $records->execute();
    $records->store_result();
    $records->bind_result($ID, $Username);
    while ($records->fetch())
    {
        $user = $Username;
    }
    $records->close();

}

?>

<!DOCTYPE html>
<html>

<body>

	<div class="header">
		<a href="/">Your App Name</a>
	</div>

	<?php if (!empty($user)): ?>

		<br />Welcome <?=$user; ?> 
		<br /><br />You are successfully logged in!
		<br /><br />
		<a href="logout.php">Logout?</a>

	<?php
else: ?>

		<h1>Please Login or Register</h1>
		<a href="login.php">Login</a> or
		<a href="register.php">Register</a>

	<?php
endif; ?>

</body>
</html>
