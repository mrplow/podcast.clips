<?php
session_start ();
if (! isset ( $_SESSION ['user_id'] )) {
	header ( "Location: /login.php" );
}

if (isset ( $_SESSION ['user_validated'] )) {
	$LastValidated = new DateTime ( $_SESSION ['user_validated'] );
	$CurrentTime = new DateTime ( 'now' );
	$SinceValidated = $LastValidated->diff ( $CurrentTime );
	$DaysSince = $SinceValidated->format ( '%a' );
	if ($DaysSince > 32) {
		header ( "Location: /validate.php" );
	}
} else {
	header ( "Location: /validate.php" );
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
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
	integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
	crossorigin="anonymous">
  </script>
<script
	src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
	integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
	crossorigin="anonymous">
  </script>
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
			<a href="/transcription.php">Search Transcriptions</a>
        <?php

								if ($_SESSION ['user_level'] <= 10) {
									echo "<br /><a href=\"/upload.php\">Upload episode</a>";
								}
								?>
      </div>
	</div>
	<div class="container">
		<h2>Segments</h2>
		<p>Type something to filter the segments</p>
		<input class="form-control" id="search" type="text"
			placeholder="Search.."> <br />


		<table
			class="table table-condensed table-sm table-hover table-striped">
			<thead>
				<tr>
					<th>Episode #</th>
					<th>Title</th>
					<th>Segment created by</th>
					<th>Comment</th>
					<th>Clip length (sec)</th>
					<th>Download</th>
				</tr>
			</thead>
			<tbody id="Segments">

            <?php
												include ('/var/connect.php');
												$dbconnect = mysqli_connect ( $GLOBALS ["mysql_hostname"], $GLOBALS ["mysql_username"], $GLOBALS ["mysql_password"], $GLOBALS ["mysql_database"] );
												if ($dbconnect->connect_error) {
													die ( "Database connection failed: " . $dbconnect->connect_error );
												}
												$episodes = mysqli_query ( $dbconnect, "SELECT ep_rowid, CAST(ep_episode_num AS FLOAT) AS ep_episode_num, ep_filename, sg_starttime, ep_title, sg_rowid, us_username, sg_comment, ROUND(sg_endtime - sg_starttime, 2) AS sg_length FROM episodes JOIN segments ON ep_rowid = sg_rowid_episode JOIN users ON sg_cby = us_rowid ORDER BY ep_episode_num, sg_starttime" ) or die ( mysqli_error ( $dbconnect ) );
												while ( $row = $episodes->fetch_assoc () ) {
													unset ( $ep_rowid, $ep_episode_num, $ep_filename, $sg_starttime, $ep_title, $sg_rowid, $us_username, $sg_comment, $sg_length );
													$ep_rowid = $row ['ep_rowid'];
													$ep_episode_num = $row ['ep_episode_num'];
													$ep_filename = $row ['ep_filename'];
													$sg_starttime = $row ['sg_starttime'];
													$ep_title = $row ['ep_title'];
													$sg_rowid = $row ['sg_rowid'];
													$us_username = $row ['us_username'];
													$sg_comment = $row ['sg_comment'];
													$sg_length = $row ['sg_length'];
													echo "
      <tr>
        <td>" . $ep_episode_num . "</td>
        <td><a href=\"/index.php?epid=" . $ep_rowid . "&timestamp=" . $sg_starttime . "\">" . $ep_title . "</a></td>
        <td>" . $us_username . "</td>
        <td>" . nl2br ( $sg_comment ) . "</td>
        <td>" . $sg_length . "</td>
        <td>
        <audio id='" . $sg_rowid . "' src='/clips/" . $sg_rowid . ".mp3' type='audio/mpeg'></audio>
        <div class=\"btn-group-vertical\">
          <button class=\"btn btn-success btn-sm\" onclick=\"document.getElementById('" . $sg_rowid . "').play()\">Play</button>
          <a class=\"btn btn-primary btn-sm\" href=\"/clips/" . $sg_rowid . ".mp3\" download=\"" . $ep_filename . " - Clip " . round ( $sg_starttime, 2 ) . "sec - " . strtok ( $sg_comment, "\r" ) . ".mp3\">Download</a>
        </div>
        </td>
      </tr>";
												}
												?>
    </tbody>
		</table>
	</div>
	<script>
$(document).ready(function(){
  $("#search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#Segments tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
</body>
</html>
