<?php
session_start();

if (isset($_SESSION['user_id']))
{
    header("Location: /");
}

include ('/var/connect.php');

$dbconnect = mysqli_connect($GLOBALS["mysql_hostname"], $GLOBALS["mysql_username"], $GLOBALS["mysql_password"], $GLOBALS["mysql_database"]);

if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}

$message = '';

if ($_POST['password'] != $_POST['confirm_password'])
{
    echo "passwords do not match";
}
else
{
    if (!empty($_POST['username']) && !empty($_POST['password']))
    {

        // Enter the new user in the database
        unset($Username, $Password);
        $Username = $_POST['username'];
        $Password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $NewUser = $dbconnect->prepare('INSERT INTO users (us_rowid_userlevel, us_username, us_password, us_cdate) VALUES (2, ?, ?, NOW())');
        $NewUser->bind_param('ss', $Username, $Password);

        if ($NewUser->execute())
        {
            $message = 'Successfully created new user';
        }
        else
        {
            $message = 'Sorry there was an issue creating your account, maybe that username is already registered';
        }

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

	<h1>Register</h1>
	<span>or <a href="login.php">login here</a></span>

	<form action="register.php" method="POST">
		
		<input type="text" placeholder="Enter your username" name="username">
		<input type="password" placeholder="and password" name="password">
		<input type="password" placeholder="confirm password" name="confirm_password">
		<input type="submit">

	</form>

</body>
</html>
