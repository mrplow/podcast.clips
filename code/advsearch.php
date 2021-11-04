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
    if ($DaysSince > 32) {
        header("Location: /validate.php");
    }
} else {
    header("Location: /validate.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>The After Disaster Podcast Clips</title>
<link rel="stylesheet"
	href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
	integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
	crossorigin="anonymous">
<meta name="viewport"
	content="width=device-width, initial-scale=1, shrink-to-fit=no">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
	crossorigin="anonymous"></script>
<script
	src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
	integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
	crossorigin="anonymous"></script>
</head>
<body>
	<div class="container">
		<div class="text-right">
        Logged in as
        <?php echo " " . $_SESSION['user_name']; ?>
        <br /> <a href="/logout.php">Logout </a> <br /> <a
				href="/changepass.php">Change Password </a>
		</div>
		<div class="float-right text-right">
			<a href="/">Home</a> <br /> <a href="/search.php">Search Clips</a> <br />
			<a href="/transcription.php">Search Transcriptions</a> <br />
			<a href="/advsearch.php">Advanced Search</a>
        <?php

                                if ($_SESSION ['user_level'] <= 10) {
                                    echo "<br /><a href=\"/upload.php\">Upload episode</a>";
                                }
                                ini_set('display_errors', 1);
                                ini_set('display_startup_errors', 1);
                                error_reporting(E_ALL);
                                ?>
      </div>
	</div>
<div class="container">
  <div class="row">
    <div class="col-sm-10">
      <div class="form-group">
        <h2>Advanced Search
        <button type="button" class="btn btn-primary" id="helpBtn">Help!</button></h2>
        <div class="position-fixed top-0 left-0 p-5" style="z-index: 5; left: 0; top: 0;">
          <div id="liveHelp" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="60000">
            <div class="toast-header">
              <strong class="mr-auto">Advanced Search Help</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body">
              All fields are optional.<br /><br />
              The numbered Transcription Text Search Term fields each have OR conditions<br /><br />
              For example:<br />
              If you put pee in the 1<sup>st</sup> Term 1 field and poo in the 2<sup>nd</sup> Term 1 field it would return results with pee OR poo.<br />
              If you also put penis in the 1<sup>st</sup> Term 2 field and vagina in 2<sup>nd</sup> the Term 2 field it would return results with (pee OR poo) AND (penis OR vagina).<br /><br />
              Yes, these examples actually return great results such as:<br />
              &quot;Cleaning poop off of a little penis or a vagina&quot;<br />
              and<br />
              &quot;Most girls I know have talked about I was laughing so hard I just started peeing and apparently the vagina is not as&quot;...<br /><br />
              Only the first 20 results are output currently
            </div>
          </div>
        </div>
        <script>
          $(document).ready(function(){
              $("#helpBtn").click(function(){
                  $("#liveHelp").toast("show");
              });
          });
        </script>
        <br />
        <form action="advsearch.php" method="get">
          <div class="form-row align-items-center">
            <legend class="col-form-label">Transcription Text</legend>
            <div class="col">
<?php
if (!empty($_GET['tr_text1'])) {
                                    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text1\" placeholder=\"Search Term 1\" value=\"" . $_GET['tr_text1'] . "\">";
                                } else {
                                    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text1\" placeholder=\"Search Term 1\">";
                                }
?>
            </div>
            <div class="col-0">
              OR
            </div>
            <div class="col">
<?php
if (!empty($_GET['tr_text1or'])) {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text1or\" placeholder=\"Search Term 1\" value=\"" . $_GET['tr_text1or'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text1or\" placeholder=\"Search Term 1\">";
}
?>
            </div>
            <div class="col-0">
              AND
            </div>
          </div>
          <div class="form-row align-items-center">
            <div class="col">
<?php
if (!empty($_GET['tr_text2'])) {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text2\" placeholder=\"Search Term 2\" value=\"" . $_GET['tr_text2'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text2\" placeholder=\"Search Term 2\">";
}
?>
            </div>
            <div class="col-0">
              OR
            </div>
            <div class="col">
<?php
if (!empty($_GET['tr_text2or'])) {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text2or\" placeholder=\"Search Term 2\" value=\"" . $_GET['tr_text2or'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text2or\" placeholder=\"Search Term 2\">";
}
?>
            </div>
            <div class="col-0">
              AND
            </div>
          </div>
          <div class="form-row align-items-center">
            <div class="col">
<?php
if (!empty($_GET['tr_text3'])) {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text3\" placeholder=\"Search Term 3\" value=\"" . $_GET['tr_text3'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text3\" placeholder=\"Search Term 3\">";
}
?>
            </div>
            <div class="col-0">
              OR
            </div>
            <div class="col">
<?php
if (!empty($_GET['tr_text3or'])) {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text3or\" placeholder=\"Search Term 3\" value=\"" . $_GET['tr_text3or'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"text\" name=\"tr_text3or\" placeholder=\"Search Term 3\">";
}
?>
            </div>
            <div class="col-0">
              AND
            </div>
          </div>
          <br />
          <div class="form-row align-items-center">
            <div class="col-10">
              <label class="col-form-label col-form-label-sm" for="ep_name">Episode name</label>
<?php
if (!empty($_GET['ep_name'])) {
    echo "<input class=\"form-control\" type=\"text\" name=\"ep_name\" value=\"" . $_GET['ep_name'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"text\" name=\"ep_name\">";
}
?>
            </div>
            <div class="col-0">
              <br />AND
            </div>
          </div>
          <div class="form-row align-items-center">
            <div class="col-5">
              <label class="col-form-label col-form-label-sm" for="ep_num_min">Minimum Ep #</label>
<?php
if (!empty($_GET['ep_num_min'])) {
    echo "<input class=\"form-control\" type=\"number\" name=\"ep_num_min\" value=\"" . $_GET['ep_num_min'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"number\" name=\"ep_num_min\">";
}
?>
            </div>
            <div class="col-5">
              <label class="col-form-label col-form-label-sm" for="ep_num_max">Maximum Ep #</label>
<?php
if (!empty($_GET['ep_num_max'])) {
    echo "<input class=\"form-control\" type=\"number\" name=\"ep_num_max\" value=\"" . $_GET['ep_num_max'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"number\" name=\"ep_num_max\">";
}
?>
            </div>
            <div class="col-0">
              <br />AND
            </div>
          </div>
          <div class="form-row align-items-center">
            <div class="col">
              <label class="col-form-label col-form-label-sm" for="start_date">Release Date Start</label>
<?php
if (!empty($_GET['start_date'])) {
    echo "<input class=\"form-control\" type=\"date\" name=\"start_date\" value=\"" . $_GET['start_date'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"date\" name=\"start_date\">";
}
?>
            </div>
            <div class="col">
              <label class="col-form-label col-form-label-sm" for="end_date">Release Date End</label>
<?php
if (!empty($_GET['end_date'])) {
    echo "<input class=\"form-control\" type=\"date\" name=\"end_date\" value=\"" . $_GET['end_date'] . "\">";
} else {
    echo "<input class=\"form-control\" type=\"date\" name=\"end_date\">";
}
?>
            </div>
          </div>
          <div class="row">
            <div class="col"><br />
              <input class="form-control btn-primary" type="submit" name="form_submit" value="Search">
            </div>
          </div>
          <input type="hidden" name="page" value="1">
        </form>
      </div>
    </div>
  </div>
</div>
<?php
include('/var/connect.php');
$dbconnect = mysqli_connect($GLOBALS ["mysql_hostname"], $GLOBALS ["mysql_username"], $GLOBALS ["mysql_password"], $GLOBALS ["mysql_database"]);

if ($dbconnect->connect_error) {
    die("Database connection failed: " . $dbconnect->connect_error);
}

                if (isset($_GET['form_submit'])) {
                    echo	"<div class=\"container\">
                 <table class=\"table table-condensed table-sm table-hover table-striped\" style=\"width: 100%\">
            <tr>
                <th><strong>Episode #</strong></th>
                <th><strong>Title</strong></th>
                <th><strong>Release Date</strong></th>
                <th><strong>Timestamp</strong></th>
                <th><strong>Transcription</strong></th>
            </tr>";
                    // pagination
                    $offset = ($_GET['page'] -1) * 20;
                    $limit = 20;

                    // always initialize a variable before use!
                    $conditions = [];
                    $parameters = [];
                    $bindtype = '';

                    // conditional statements
                    if (!empty($_GET['ep_name'])) {
                        $conditions[] = 'ep_title LIKE ?';
                        $parameters[] = '%'.$_GET['ep_name'].'%';
                        $bindtype .= 's';
                    }

                    if (!empty($_GET['tr_text1']) && empty($_GET['tr_text1or'])) {
                        $conditions[] = 'tr_text LIKE ?';
                        $parameters[] = '%'.$_GET['tr_text1'].'%';
                        $bindtype .= 's';
                    }

                    if (!empty($_GET['tr_text1']) && !empty($_GET['tr_text1or'])) {
                        $conditions[] = '(tr_text LIKE ? OR tr_text LIKE ?)';
                        $parameters[] = '%'.$_GET['tr_text1'].'%';
                        $parameters[] = '%'.$_GET['tr_text1or'].'%';
                        $bindtype .= 'ss';
                    }

                    if (!empty($_GET['tr_text2']) && empty($_GET['tr_text2or'])) {
                        $conditions[] = 'tr_text LIKE ?';
                        $parameters[] = '%'.$_GET['tr_text2'].'%';
                        $bindtype .= 's';
                    }

                    if (!empty($_GET['tr_text2']) && !empty($_GET['tr_text2or'])) {
                        $conditions[] = '(tr_text LIKE ? OR tr_text LIKE ?)';
                        $parameters[] = '%'.$_GET['tr_text2'].'%';
                        $parameters[] = '%'.$_GET['tr_text2or'].'%';
                        $bindtype .= 'ss';
                    }

                    if (!empty($_GET['tr_text3']) && empty($_GET['tr_text3or'])) {
                        $conditions[] = 'tr_text LIKE ?';
                        $parameters[] = '%'.$_GET['tr_text3'].'%';
                        $bindtype .= 's';
                    }

                    if (!empty($_GET['tr_text3']) && !empty($_GET['tr_text3or'])) {
                        $conditions[] = '(tr_text LIKE ? OR tr_text LIKE ?)';
                        $parameters[] = '%'.$_GET['tr_text3'].'%';
                        $parameters[] = '%'.$_GET['tr_text3or'].'%';
                        $bindtype .= 'ss';
                    }

                    if (!empty($_GET['ep_num_min'])) {
                        $conditions[] = 'ep_episode_num >= ?';
                        $parameters[] = $_GET['ep_num_min'];
                        $bindtype .= 'i';
                    }

                    if (!empty($_GET['ep_num_max'])) {
                        $conditions[] = 'ep_episode_num <= ?';
                        $parameters[] = $_GET['ep_num_max'];
                        $bindtype .= 'i';
                    }

                    if (!empty($_GET['start_date'])) {
                        $conditions[] = 'ep_release_date >= ?';
                        $parameters[] = $_GET['start_date'];
                        $bindtype .= 's';
                    }

                    if (!empty($_GET['end_date'])) {
                        $conditions[] = 'ep_release_date <= ?';
                        $parameters[] = $_GET['end_date'];
                        $bindtype .= 's';
                    }

                    // the main query
                    $query = "SELECT ep_rowid, CAST(ep_episode_num AS DOUBLE) AS ep_episode_num, ep_title, ep_release_date, SUBSTRING(TIME_FORMAT(SEC_TO_TIME(tr_time), '%H:%i:%s.%f'), 1, 11) AS tr_time, tr_text FROM episodes JOIN transcriptions ON ep_rowid = tr_rowid_episode";

                    // add all conditions, if any
                    if ($conditions) {
                        $query .= " WHERE ".implode(" AND ", $conditions);
                    }

                    // add the order by
                    $query .=' ORDER BY ep_episode_num, tr_time';

                    // a search query always needs at least a LIMIT clause
                    $query .= ' LIMIT ?, ?';
                    $parameters[] = $offset;
                    $parameters[] = $limit;
                    $bindtype .= 'i';
                    $bindtype .= 'i';
                    $stmt = $dbconnect->prepare($query);
                    $stmt->bind_param($bindtype, ...$parameters);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $output = array();
                    $i=0;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row['ep_episode_num'] . "</td>";
                        echo "<td>" . $row['ep_title'] . "</td>";
                        echo "<td class=\"text-nowrap\">" . $row['ep_release_date'] . "</td>";
                        echo "<td><a href=\"/index.php?epid=" . $row['ep_rowid'] . "&timestamp=" . $row['tr_time'] . "\">" . $row['tr_time'] . "</a></td>";
                        echo "<td>" . $row['tr_text'] . "</td></tr>";
                        $i++;
                    }
                }
?>
</table>
</div>
</html>
