<?php
include ('/var/connect.php');

$dbconnect = mysqli_connect($hostname, $username, $password, $db);

if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}

echo $_POST['Delete'];
if (isset($_POST['Delete']))
{
    $del_rowid = $_POST['Delete'];

    $DelSegStm = $dbconnect->prepare('DELETE segments FROM segments WHERE sg_rowid = ?');
    $DelSegStm->bind_param('i', $del_rowid);
    $DelSegStm->execute();

}

?>

