<?php
include ('/var/connect.php');

$dbconnect = mysqli_connect($GLOBALS["mysql_hostname"], $GLOBALS["mysql_username"], $GLOBALS["mysql_password"], $GLOBALS["mysql_database"]);

if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}

$uploaded_file_name = $argv[1];
$filename = $argv[2];
$new_rowid = $argv[3];

echo shell_exec("/usr/bin/ffmpeg -i /var/www/podcasts/\"" . $filename . ".mp3\" /var/www/podcasts/\"" . $filename . ".jpg\" > /dev/null 2>&1");
echo shell_exec("/usr/local/bin/audiowaveform -i \"/var/www/podcasts/" . $uploaded_file_name . "\" -o \"/var/www/podcasts/" . $filename . ".json\" -z 20000 -b 8 > /dev/null 2>&1");
echo shell_exec("/usr/local/bin/audiowaveform -i \"/var/www/podcasts/" . $uploaded_file_name . "\" -o \"/var/www/podcasts/" . $filename . ".dat\" -z 512 -b 8 > /dev/null 2>&1");
echo shell_exec("/usr/bin/autosub -F json -o /tmp/\"" . $filename . ".json\" /var/www/podcasts/\"" . $filename . ".mp3\" > /dev/null 2>&1");
$jsondata = file_get_contents("/tmp/" . $filename . ".json");
$array = json_decode($jsondata, true);

foreach($array as $item) {
    $timestamp = $item['start'];
    $text = $item['content'];

    $CrTranscription = $dbconnect->prepare("INSERT INTO transcriptions (tr_rowid_episode, tr_time, tr_text) VALUES(?, ?, ?)");
    $CrTranscription->bind_param('ids', $new_rowid, $timestamp, $text);
    $CrTranscription->execute();
}
?>
