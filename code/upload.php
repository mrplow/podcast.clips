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
                $CheckExist->bind_param('d', $_POST['episode_num']);
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
                        $SHA1Upload = exec("mp3hash -q ".$_FILES["file"]["tmp_name"]);
                        unset($ResultEpisodeHash);
                        $CheckHashExist = $dbconnect->prepare('SELECT ep_file_sha1 FROM episodes WHERE ep_file_sha1 = ?');
                        $CheckHashExist->bind_param('s', $SHA1Upload);
                        $CheckHashExist->execute();
                        $CheckHashExist->store_result();
                        $CheckHashExist->bind_result($EpHash);
                        while ($CheckHashExist->fetch())
                        {
                            $ResultEpisodeHash = $EpHash;
                        }
                        $CheckHashExist->close();
                        if (isset($ResultEpisodeHash))
                        {
                            echo "Episode hash " . $ResultEpisodeHash . " already exists, nothing done.<br />";
                        }
                        else
                        {
                            $tmp_name = $_FILES["file"]["tmp_name"];
                            $uploaded_file_name = $_FILES["file"]["name"];
                            $ep_episode_num = $_POST['episode_num'];
                            $ep_release_date = $_POST['episode_date'];
                            $ep_title = htmlspecialchars($_POST['episode_title']);
                            $ep_description = htmlspecialchars($_POST['episode_description']);
                            move_uploaded_file($tmp_name, "/var/www/podcasts/" . $uploaded_file_name);
                            $CrEpisode = $dbconnect->prepare("INSERT INTO episodes (ep_filename, ep_file_sha1, ep_episode_num, ep_release_date, ep_title, ep_description) VALUES( ?, ?, ?, ?, ?, ?)");
                            $CrEpisode->bind_param('ssdsss', $filename, $SHA1Upload, $ep_episode_num, $ep_release_date, $ep_title, $ep_description);
                            $CrEpisode->execute();
                            $new_rowid = $CrEpisode->insert_id;
                            echo "Success!<br />";
                            echo shell_exec("nohup php /var/www/code/process_upload.php \"".$uploaded_file_name."\" \"".$filename."\" ".$new_rowid." > /dev/null &");
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
        <a href="/search.php">Search Clips</a>
        <br />
        <a href="/transcription.php">Search Transcriptions</a>
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
          <input form="upload" class="form-control" type="number" id="episode_num" name="episode_num" min="0" step=".1" required="true"><br />
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
