<?php
session_start();

include('/var/connect.php');

$dbconnect = mysqli_connect($GLOBALS ["mysql_hostname"], $GLOBALS ["mysql_username"], $GLOBALS ["mysql_password"], $GLOBALS ["mysql_database"]);

if ($dbconnect->connect_error) {
    die("Database connection failed: " . $dbconnect->connect_error);
}

$LoggedIn = $dbconnect->prepare('SELECT us_rowid, us_username FROM users WHERE us_rowid = ?');
$LoggedIn->bind_param('i', $_SESSION ['user_id']);
$LoggedIn->execute();
$LoggedIn->store_result();
$LoggedIn->bind_result($ID, $Username);
while ($LoggedIn->fetch()) {
    $LoggedInID = $ID;
    $LoggedInUser = $Username;
}
$LoggedIn->close();

if (isset($_POST ['Save'])) {
    if (is_numeric($_POST ['Save'])) {
        $upd_by = $LoggedInID;
        $upd_comment = htmlspecialchars($_POST ['Comment']);
        $upd_start = $_POST ['StartTime'];
        $upd_end = $_POST ['EndTime'];
        $upd_rowid = $_POST ['Save'];
        $UpdSegStm = $dbconnect->prepare('UPDATE segments SET sg_mby = ?, sg_mdate = NOW(), sg_comment = ?, sg_starttime = ?, sg_endtime = ? WHERE sg_rowid = ? AND sg_cby = ?');
        $UpdSegStm->bind_param('isddii', $upd_by, $upd_comment, $upd_start, $upd_end, $upd_rowid, $upd_by);
        $UpdSegStm->execute();
        $sg_StartTime = FLOOR($upd_start / 60.0) . "." . ROUND(((($upd_start / 60.0) - FLOOR($upd_start / 60.0)) * 60), 2);
        $sg_EndTime = FLOOR($upd_end / 60.0) . "." . ROUND(((($upd_end / 60.0) - FLOOR($upd_end / 60.0)) * 60), 2);
        $ep_filename = $_POST ['EpisodeFilename'];
        echo shell_exec("/usr/bin/mp3splt -Q -o ../clips/" . $upd_rowid . " \"/var/www/podcasts/" . $ep_filename . ".mp3\" " . $sg_StartTime . " " . $sg_EndTime . " 2>&1");
    } else {
        $cr_eprowid = $_POST ['EpisodeRowid'];
        $cr_by = $_SESSION ['user_id'];
        $cr_comment = htmlspecialchars($_POST ['Comment']);
        $cr_start = $_POST ['StartTime'];
        $cr_end = $_POST ['EndTime'];
        $CrSegStm = $dbconnect->prepare('INSERT INTO segments (sg_rowid_episode, sg_cby, sg_cdate, sg_comment, sg_starttime, sg_endtime) VALUES( ?, ?, NOW(), ?, ?, ? )');
        $CrSegStm->bind_param('iisdd', $cr_eprowid, $cr_by, $cr_comment, $cr_start, $cr_end);
        $CrSegStm->execute();
        $new_rowid = $CrSegStm->insert_id;
        $sg_StartTime = FLOOR($cr_start / 60.0) . "." . ROUND(((($cr_start / 60.0) - FLOOR($cr_start / 60.0)) * 60), 2);
        $sg_EndTime = FLOOR($cr_end / 60.0) . "." . ROUND(((($cr_end / 60.0) - FLOOR($cr_end / 60.0)) * 60), 2);
        $ep_filename = $_POST ['EpisodeFilename'];
        echo shell_exec("/usr/bin/mp3splt -Q -o ../clips/" . $new_rowid . " \"/var/www/podcasts/" . $ep_filename . ".mp3\" " . $sg_StartTime . " " . $sg_EndTime . " 2>&1");
    }
}
if (isset($_POST ['Delete'])) {
    $del_rowid = $_POST ['Delete'];
    $upd_by = $LoggedInID;
    $DelSegStm = $dbconnect->prepare('DELETE segments FROM segments WHERE sg_rowid = ? AND sg_cby = ?');
    $DelSegStm->bind_param('ii', $del_rowid, $upd_by);
    $DelSegStm->execute();
    echo shell_exec("/bin/rm \"/var/www/clips/" . $del_rowid . ".mp3\" 2>&1");
}
if (isset($_POST ['Export'])) {
    $ep_filename = $_POST ['EpisodeFilename'];
    $sg_StartTime = FLOOR($_POST ['ExportStartTime'] / 60.0) . "." . ROUND(((($_POST ['ExportStartTime'] / 60.0) - FLOOR($_POST ['ExportStartTime'] / 60.0)) * 60), 2);
    $sg_EndTime = FLOOR($_POST ['ExportEndTime'] / 60.0) . "." . ROUND(((($_POST ['ExportEndTime'] / 60.0) - FLOOR($_POST ['ExportEndTime'] / 60.0)) * 60), 2);
    $sg_Comment = $_POST ['Comment'];
    echo shell_exec("/usr/bin/mp3splt -Q -o ../clips/" . $_POST ['Export'] . " \"/var/www/podcasts/" . $ep_filename . ".mp3\" " . $sg_StartTime . " " . $sg_EndTime . " 2>&1");
    header("Cache-Control: private");
    header("Content-type: audio/mpeg3");
    header("Content-Transfer-Encoding: binary");
    header('Content-Disposition: attachment; filename="' . $ep_filename . ' - Clip ' . round($sg_StartTime, 2) . 'sec - ' . str_replace(array(
            "\r",
            "\n"
    ), ' ', $sg_Comment) . '.mp3"');
    readfile('/var/www/clips/' . $_POST ['Export'] . '.mp3');
}
echo "<script type='text/javascript'>window.parent.location.href = \"/index.php?epid=" . $_POST ['EpisodeRowid'] . "\"</script>";
