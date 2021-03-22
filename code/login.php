<?php
session_start();

if (isset($_SESSION['user_id']))
{
    header("Location: /index2.php");
}

include ('/var/connect.php');

$dbconnect = mysqli_connect($GLOBALS["mysql_hostname"], $GLOBALS["mysql_username"], $GLOBALS["mysql_password"], $GLOBALS["mysql_database"]);

if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}

if (!empty($_POST['username']) && !empty($_POST['password']))
{

    unset($Username, $Password, $ReturnedID);
    $Username = $_POST['username'];
    $Password = $_POST['password'];

    $records = $dbconnect->prepare('SELECT us_rowid, us_username, us_password FROM users WHERE us_username = ?');
    $records->bind_param('s', $Username);
    $records->execute();
    $records->store_result();
    $records->bind_result($ID, $Username, $Password);
    while ($records->fetch())
    {
        $ReturnedID = $ID;
        $ReturnedUsername = $Username;
        $ReturnedPassword = $Password;
    }
    $records->close();

    $message = '';
    if (!empty($ReturnedID) && password_verify($_POST['password'], $ReturnedPassword))
    {

        $_SESSION['user_id'] = $ReturnedID;
        header("Location: /index2.php");

    }
    else
    {
        $message = 'Sorry, those credentials do not match';
    }

}

?>

<!DOCTYPE html>
<html>
<body>

	<?php if (!empty($message)): ?>
		<p><?=$message
?></p>
	<?php
endif; ?>

	<h1>Login</h1>
	<span>or <a href="register.php">register here</a></span>

	<form action="login.php" method="POST">
		
		<input type="text" placeholder="Enter your username" name="username">
		<input type="password" placeholder="and password" name="password">

		<input type="submit">

	</form>

</body>
</html>
