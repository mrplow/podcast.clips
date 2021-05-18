<?php
session_start();
if ((!isset($_SESSION['user_id'])) || ($_SESSION['user_level'] > 1))
{
    header("Location: /login.php");
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>AD Demo Page
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous">
    </script>
  </head>
  <body>
<?php
if (isset($_POST['add']))
{
echo "    <div class=\"container\">";
    $allowedExts = "mp3";
    $temp = pathinfo($_FILES["file"]["name"]);
    $extension = $temp['extension'];
    $filename = $temp['filename'];
    if ((($_FILES["file"]["type"] == "audio/mp3") || ($_FILES["file"]["type"] == "audio/mpeg")) && ($extension == $allowedExts))
    {
        if ($_FILES["file"]["error"] > 0)
        {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
        }
        else
        {
            echo "Upload: " . $_FILES["file"]["name"] . "<br>";
            echo "Size: " . round(($_FILES["file"]["size"] / 1024 / 1024), 2) . " MB<br>";
            if (file_exists("/var/www/podcasts/" . $_FILES["file"]["name"]))
            {
                echo $_FILES["file"]["name"] . " already exists. ";
            }
            else
            {
                include ('/var/connect.php');

                $dbconnect = mysqli_connect($GLOBALS["mysql_hostname"], $GLOBALS["mysql_username"], $GLOBALS["mysql_password"], $GLOBALS["mysql_database"]);

                if ($dbconnect->connect_error)
                {
                    die("Database connection failed: " . $dbconnect->connect_error);
                }
                unset($ResultEpisodeNum);
                $CheckExist = $dbconnect->prepare('SELECT ep_episode_num FROM episodes WHERE ep_episode_num = ?');
                $CheckExist->bind_param('i', $_POST['episode_num']);
                $CheckExist->execute();
                $CheckExist->store_result();
                $CheckExist->bind_result($EpNum);
                while ($CheckExist->fetch())
                {
                    $ResultEpisodeNum = $EpNum;
                }
                $CheckExist->close();
                if (isset($ResultEpisodeNum))
                {
                    echo "Episode # " . $ResultEpisodeNum . " already exists, nothing done.<br />";
                }
                else
                {
                    if (($_POST['episode_date'] == '') || ($_POST['episode_num'] == '') || !isset($_POST['episode_date'], $_POST['episode_num']))
                    {
                        echo "Episode Number and Dropped date are required, nothing done.<br />";
                    }
                    else
                    {
                        move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/podcasts/" . $_FILES["file"]["name"]);
                        echo shell_exec("/usr/local/bin/audiowaveform -i \"/var/www/podcasts/" . $_FILES["file"]["name"] . "\" -o \"/var/www/podcasts/" . $filename . ".json\" -z 20000 -b 8 > /dev/null 2>&1");
                        echo shell_exec("/usr/local/bin/audiowaveform -i \"/var/www/podcasts/" . $_FILES["file"]["name"] . "\" -o \"/var/www/podcasts/" . $filename . ".dat\" -z 512 -b 8 > /dev/null 2>&1");
                        $ep_filename = $filename;
                        $ep_episode_num = $_POST['episode_num'];
                        $ep_release_date = $_POST['episode_date'];
                        $ep_title = htmlspecialchars($_POST['episode_title']);
                        $ep_description = htmlspecialchars($_POST['episode_description']);
                        $CrEpisode = $dbconnect->prepare("INSERT INTO episodes (ep_filename, ep_episode_num, ep_release_date, ep_title, ep_description) VALUES( ?, ?, '$ep_release_date', ?, ?)");
                        $CrEpisode->bind_param('siss', $ep_filename, $ep_episode_num, $ep_title, $ep_description);
                        $CrEpisode->execute();
                        $new_rowid = $CrEpisode->insert_id;
                        echo "Success! <br />";
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
                    }
                }
            }
        }
    }
    else
    {
        echo "Invalid file " . $_FILES["file"]["type"];
    }
echo "    </div>";
}
?>
    <div class="container">
      <div class="text-right">
        Logged in as 
        <?php echo $_SESSION['user_name']; ?>
        <br />
        <a href="/logout.php">Logout
        </a>
        <br />
        <a href="/changepass.php">Change Password
        </a>
      </div>
      <div class="float-right text-right">
        <a href="/">Home</a>
        <br />
        <a href="/search.php">Search</a>
        <?php if ($_SESSION['user_level'] == 1)
{
    echo "<br /><a href=\"/upload.php\">Upload episode</a>";
}
?>
      </div>
      <form id="upload" action="?" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <input form="upload" class="form-control" name="file" type="file" id="file" required="true"><br />
          <label for="episode_num">Episode Number</label>
          <input form="upload" class="form-control" type="number" id="episode_num" name="episode_num" min="0" required="true"><br />
          <label for="episode_date">Dropped</label>
          <input form="upload" class="form-control" type="date" id="episode_date" name="episode_date" required="true"><br />
          <label for="episode_title">Title</label>
          <input form="upload" class="form-control" type="text" id="episode_title" name="episode_title" maxlength="256"><br />
          <label for="episode_description">Description</label>
          <textarea form="upload" class="form-control" id="episode_description" name="episode_description" maxlength="4000" rows="5"></textarea><br />
          <input form="upload" class="form-control" type="submit" name="add" value="Add episode">
        </div>
      </form>
    </div>
  </body>
</html>
