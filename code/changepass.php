<?php
session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
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
    if (!empty($_POST['oldpassword']) && !empty($_POST['password']))
    {

        unset($OldPassword);
        $OldPassword = $_POST['oldpassword'];

        $records = $dbconnect->prepare('SELECT us_rowid, us_password FROM users WHERE us_rowid = ?');
        $records->bind_param('i', $_SESSION['user_id']);
        $records->execute();
        $records->store_result();
        $records->bind_result($UserRowid, $HashedPassword);
        while ($records->fetch())
        {
            $ReturnedID = $UserRowid;
            $ReturnedPassword = $HashedPassword;
        }
        $records->close();

        $message = '';
        if (!empty($ReturnedID) && password_verify($_POST['oldpassword'], $ReturnedPassword))
        {
            $NewPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $UpdatePass = $dbconnect->prepare('UPDATE users SET us_password = ?, us_mdate = NOW() WHERE us_rowid = ?');
            $UpdatePass->bind_param('si', $NewPassword, $_SESSION['user_id']);
            $UpdatePass->execute();
            $message = 'Password updated, redirecting back home...';
            header( "refresh:3; /" ); 
        }
        else
        {
            $message = 'Sorry, those credentials do not match';
        }
    }


}
?>

<!DOCTYPE html>
<html>
<body>
    <style>
      body {
        font-family: 'Helvetica neue', Helvetica, Arial, sans-serif;
      }

      button, select, textarea, input {
        background-color: white;
        border: 2px solid #008CBA;
        color: black;
        padding: 16px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 18px;
        margin: 4px 2px;
        transition-duration: 0.4s;
        cursor: pointer;
      }

      button:hover, select:hover, textarea:hover, input:hover {
        background-color: #008CBA;
        color: white;
      }
    </style>

<?php if (!empty($message))
{ ?>
<p><?=$message
?></p>
<?php
} ?>

	<h1>Change Password for <?php echo $_SESSION['user_name']; ?></h1>
	<span>or go <a href="index.php">back home</a></span>
<br /><h2>Don't forget your password, there is no email reset.</h2><br />
	<form action="changepass.php" method="POST">
		<input type="password" placeholder="old password" name="oldpassword">		
		<input type="password" placeholder="new password" name="password">
		<input type="password" placeholder="confirm new password" name="confirm_password">
		<input type="submit">

	</form>

</body>
</html>

