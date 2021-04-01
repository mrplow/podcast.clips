<?php
session_start();
if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>AD Demo Page
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <style>
      .hide {
        display: none;
      }
      .segmentrow {
        line-height: 80px;
        min-height: 80px;
        height: 80px;
      }
      audio {
        width: 100%;
      }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous">
  </script>
  </head>
  <body>
    <div class="container">
      <form action='?' method='post'>
        <div class="form-group">
          <label for="id">Choose an episode
          </label>
          <select class="form-control" name='id' onchange="this.form.elements['formEpisode'].click();">
            <option disabled hidden='' selected value=''>
            </option>
            <?php
include ('/var/connect.php');
$dbconnect = mysqli_connect($GLOBALS["mysql_hostname"], $GLOBALS["mysql_username"], $GLOBALS["mysql_password"], $GLOBALS["mysql_database"]);
if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}
$episodes = mysqli_query($dbconnect, "SELECT
ep_rowid,
CONCAT(ep_episode_num, ' - ', ep_title) AS ep_title
FROM
episodes
ORDER BY
ep_episode_num") or die(mysqli_error($dbconnect));
while ($row = $episodes->fetch_assoc())
{
    unset($ep_rowid, $ep_title);
    $ep_rowid = $row['ep_rowid'];
    $ep_title = $row['ep_title'];
    echo '
<option value="' . $ep_rowid . '">
' . $ep_title . '
</option>';
}
?>
          </select> 
          <input class="d-none" name='formEpisode' type='submit' value='Select'>
        </div>   
      </form>
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
    </div>
    <?php if (isset($_GET['epid'])) {
              $selected_ep_rowid=$_GET['epid'];
          }
          if (isset($_POST['formEpisode'])) {
              $selected_ep_rowid = $_POST['id'];
          }
          if (isset($selected_ep_rowid)): ?>
    <?php
        $selected_episode = $dbconnect->prepare("SELECT ep_filename, ep_episode_num, ep_release_date, ep_title, ep_description FROM episodes WHERE ep_rowid = ?");
        unset($ep_filename, $ep_title, $ep_description, $ep_episode_num, $ep_release_date);
        $selected_episode->bind_param('i', $selected_ep_rowid);
        $selected_episode->execute();
        $selected_episode->store_result();
        $selected_episode->bind_result($filename, $episode_num, $release_date, $title, $description);
        while ($selected_episode->fetch())
        {
            $ep_filename = $filename;
            $ep_title = htmlspecialchars($title, ENT_QUOTES);
            $ep_description = htmlspecialchars($description, ENT_QUOTES);
            $ep_episode_num = $episode_num;
            $ep_release_date = $release_date;
        }
        $selected_episode->close();
?>
    <div class="container" id='titles'>
      <h1>Episode 
        <?php echo $ep_episode_num . ": " . $ep_title; ?>
      </h1>
      <h3>Released 
        <?php echo $ep_release_date; ?>
      </h3>
      <h4>
        <?php echo $ep_description; ?>
      </h4>
<?php 
if (file_exists("/var/www/podcasts/" . $ep_filename . ".jpg"))
{
    echo "<img class='img-fluid' src=/podcasts/" . $ep_filename . ".jpg>";
}
?>
    </div>
    <div class="container">
      <div id='waveform-container'>
        <div id='zoomview-container'>
        </div>
        <div id='overview-container'>
        </div>
      </div>
    </div>
    <div class="container">
      <div id='media-controls'>
        <audio id='audio' controls='controls'>
          <source src='/podcasts/<?php echo $ep_filename; ?>.mp3' type='audio/mpeg'>
          Your browser does not support the audio element.
        </audio>
      </div>
      <div id='media-controls'>
        <button type="button" class="btn btn-primary btn-lg btn-block" data-action='add-segment'>Add a Segment at current time
        </button>
        <br>
      </div>
    </div>
    <hr/>
    <div class="container">
      <div id='media-controls' class="form-row">
        <div class="col">
          <button type="button" class="btn btn-secondary btn-sm" data-action='zoom-in'>Zoom in
          </button>
          <button type="button" class="btn btn-secondary btn-sm" data-action='zoom-out'>Zoom out
          </button>
        </div>
        <div class="col">
          <label for='amplitude-scale'>Amplitude scale
          </label>
          <input type='range' id='amplitude-scale' min='0' max='10' step='1'>
        </div>
      </div>
      <div  id='media-controls' class="form-row">
        <div class="col">
          <input type='text' id='seek-time' value='0.0'>
          <button type="button" class="btn btn-secondary btn-sm" data-action='seek'>Jump to (sec)
          </button>  
        </div>  
        <div class="col">
          <input type='checkbox' id='auto-scroll' checked>
          <label for='auto-scroll'>Auto-scroll
          </label>
        </div>
      </div>
    </div>
    <hr/>
    <div>
      <div class="hide container table-responsive " id='segments'>
        <h2>Segments
        </h2>
        <table class="table table-condensed table-sm table-hover table-striped">
          <thead>
            <tr>
              <th>Created by
              </th>
              <th class="w-50">Comment
              </th>
              <th>Time (seconds)
              </th>
              <th>
              </th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
    </div>
  <script src='peaks.js'>
  </script>
  <script>
    (function(Peaks) {
      var renderSegments = function(peaks) {
        var segmentsContainer = document.getElementById('segments');
        var segments = peaks.segments.getSegments();
        var html = '';
        for (var i = 0; i < segments.length; i++) {
          var segment = segments[i];
          var row = '<form action="segment.php" id="segment' + segment.id + '" target="delete-segment" method="post">' +
              '        <tr class="segmentrow">' +
              '          <td>' + segment.createdBy + '<br />' +
              '          </td>' +
              '        <td>' +
              '          <div class="form-group form-control-sm">' +
              '            <textarea class="form-control" form="segment' + segment.id + '" name="Comment" rows="5" cols="30" maxlength="256" data-action="update-segment-label" data-id="' + segment.id + '"/>' + segment.labelText + '</textarea>' +
              '            </div>' +
              '          </td>' +
              '        <td>' +
              '          <div class="form-group form-control-sm">' +
              '            <label for="starttime">Start</label>' +
              '            <input id="starttime' + segment.id + '" class="form-control" form="segment' + segment.id + '" name="StartTime" data-action="update-segment-start-time" type="number" value="' + segment.startTime + '" data-id="' + segment.id + '"/>' +
              '            <label for="endtime">End</label>' +
              '            <input id="endtime' + segment.id + '" class="form-control" form="segment' + segment.id + '" name="EndTime" data-action="update-segment-end-time" type="number" value="' + segment.endTime + '" data-id="' + segment.id + '"/>' +
              '          </div>' +
              '        </td>' +
              '        <td>' +
              '          <div class="btn-group-vertical">' +
              '            <a href="#' + segment.id + '" data-action="play-segment" class="btn btn-success btn-sm" data-id="' + segment.id + '">Play</a>' +
              '            <a href="#' + segment.id + '" data-action="loop-segment" class="btn btn-info btn-sm" data-id="' + segment.id + '">Loop</a>' +
              '            <button form="segment' + segment.id + '" class="btn btn-primary btn-sm" name="Save" value="' + segment.id + '"/>Save</button>' +
              '            <button onclick="return confirm(\'Are you sure you want to delete the segment?\');" form="segment' + segment.id + '" class="btn btn-danger btn-sm" name="Delete" value="' + segment.id + '"/>Delete</button><button form="segment' + segment.id + '" class="btn btn-primary btn-sm" name="Export" value="' + segment.id + '"/>Download</button>' +
              '          </div>' +
              '        </td>' +
              '        <input type="hidden" form="segment' + segment.id + '" name="EpisodeRowid" value="<?php echo $selected_ep_rowid; ?>">' +
              '        <input type="hidden" form="segment' + segment.id + '" name="ExportStartTime" value="' + segment.startTime + '">' +
              '        <input type="hidden" form="segment' + segment.id + '" name="ExportEndTime" value="' + segment.endTime + '">' +
              '        <input type="hidden" form="segment' + segment.id + '" name="EpisodeFilename" value="<?php echo $ep_filename; ?>">' +
              '        <input type="hidden" form="segment' + segment.id + '" name="UserRowid" value="' + segment.createdBy + '">' +
              '      </tr>' +
              '    </form>';
          html += row;
        }
        segmentsContainer.querySelector('tbody').innerHTML = html;
        if (html.length) {
          segmentsContainer.classList.remove('hide');
        }
        document.querySelectorAll('input[data-action="update-segment-start-time"]').forEach(function(inputElement) {
          inputElement.addEventListener('input', function(event) {
            var element = event.target;
            var id = element.getAttribute('data-id');
            var segment = peaks.segments.getSegment(id);
            if (segment) {
              var startTime = parseFloat(element.value);
              if (startTime < 0) {
                startTime = 0;
                element.value = 0;
              }
              if (startTime >= segment.endTime) {
                startTime = segment.endTime - 0.1;
                element.value = startTime;
              }
              segment.update({
                startTime: startTime }
                            );
            }
          }
                                       );
        }
                                                                                           );
        document.querySelectorAll('input[data-action="update-segment-end-time"]').forEach(function(inputElement) {
          inputElement.addEventListener('input', function(event) {
            var element = event.target;
            var id = element.getAttribute('data-id');
            var segment = peaks.segments.getSegment(id);
            if (segment) {
              var endTime = parseFloat(element.value);
              if (endTime < 0) {
                endTime = 0;
                element.value = 0;
              }
              if (endTime <= segment.startTime) {
                endTime = segment.startTime + 0.1;
                element.value = endTime;
              }
              segment.update({
                endTime: endTime }
                            );
            }
          }
                                       );
        }
                                                                                         );
        document.querySelectorAll('input[data-action="update-segment-label"]').forEach(function(inputElement) {
          inputElement.addEventListener('input', function(event) {
            var element = event.target;
            var id = element.getAttribute('data-id');
            var segment = peaks.segments.getSegment(id);
            var labelText = element.labelText;
            if (segment) {
              segment.update({
                labelText: labelText }
                            );
            }
          }
                                       );
        }
                                                                                      );
      };
      var options = {
        containers: {
          zoomview: document.getElementById('zoomview-container'),
          overview: document.getElementById('overview-container')
        }
        ,
        mediaElement: document.getElementById('audio'),
        dataUri: {
          arraybuffer: 'podcasts/<?php echo $ep_filename; ?>.dat',
          json: 'podcasts/<?php echo $ep_filename; ?>.json'
        }
        ,
        height: 100,
        keyboard: true,
        showPlayheadTime: true
      };
      Peaks.init(options, function(err, peaksInstance) {
        if (err) {
          console.error(err.message);
          return;
        }
        console.log("Peaks instance ready");
        document.querySelector('[data-action="zoom-in"]').addEventListener('click', function() {
          peaksInstance.zoom.zoomIn();
        }
                                                                          );
        document.querySelector('[data-action="zoom-out"]').addEventListener('click', function() {
          peaksInstance.zoom.zoomOut();
        }
                                                                           );
        var segmentCounter = 1;
        document.querySelector('button[data-action="add-segment"]').addEventListener('click', function() {
          peaksInstance.segments.add({
            startTime: peaksInstance.player.getCurrentTime(),
            endTime: peaksInstance.player.getCurrentTime() + 6,
            labelText: 'Segment ' + segmentCounter++,
            editable: true
          }
                                    );
          renderSegments(peaksInstance);
        }
                                                                                    );
        document.querySelector('button[data-action="seek"]').addEventListener('click', function(event) {
          var time = document.getElementById('seek-time').value;
          var seconds = parseFloat(time);
          if (!Number.isNaN(seconds)) {
            peaksInstance.player.seek(seconds);
          }
        }
                                                                             );
        document.getElementById('auto-scroll').addEventListener('change', function(event) {
          var view = peaksInstance.views.getView('zoomview');
          view.enableAutoScroll(event.target.checked);
        }
                                                               );
        document.querySelector('body').addEventListener('click', function(event) {
          var element = event.target;
          var action  = element.getAttribute('data-action');
          var id      = element.getAttribute('data-id');
          if (action === 'play-segment') {
            var segment = peaksInstance.segments.getSegment(id);
            peaksInstance.player.playSegment(segment);
          }
          else if (action === 'loop-segment') {
            var segment = peaksInstance.segments.getSegment(id);
            peaksInstance.player.playSegment(segment, true);
          }
        }
                                                       );
        <?php
        $segments = mysqli_query($dbconnect, "SELECT
                                 sg_rowid,
                                 cby.us_username AS cby,
                                 sg_cdate,
                                 mby.us_username AS mby,
                                 sg_mdate,
                                 sg_comment,
                                 sg_starttime,
                                 sg_endtime
                                 FROM
                                 segments
                                 JOIN users AS cby
                                 ON
                                 sg_cby = cby.us_rowid
                                 LEFT JOIN users AS mby
                                 ON
                                 sg_mby = mby.us_rowid
                                 WHERE
                                 sg_rowid_episode = $selected_ep_rowid
                                 ORDER BY sg_starttime") or die(mysqli_error($dbconnect));
        while ($row = mysqli_fetch_array($segments))
        {
            unset($sg_rowid, $cby, $sg_cdate, $mby, $sg_mdate, $sg_comment, $sg_starttime, $sg_endtime);
            $sg_rowid = $row['sg_rowid'];
            $cby = htmlspecialchars("" . $row['cby'] . "", ENT_QUOTES);
            $sg_cdate = $row['sg_cdate'];
            $mby = htmlspecialchars("" . $row['mby'] . "", ENT_QUOTES);
            $sg_mdate = $row['sg_mdate'];
            $sg_comment = str_replace("\n", "&#010;", str_replace("\r", "&#010;", str_replace("\r\n", "&#010;", htmlspecialchars($row['sg_comment'], ENT_QUOTES))));
            $sg_starttime = $row['sg_starttime'];
            $sg_endtime = $row['sg_endtime'];
            echo "peaksInstance.segments.add({
          id: " . $sg_rowid . ",
            startTime: " . $sg_starttime . ",
              endTime: " . $sg_endtime . ",
                createdBy: '" . $cby . "',
                  labelText: '" . $sg_comment . "',
                    editable: true
        }
        );
        renderSegments(peaksInstance);
        ";
        }
?>
                 var amplitudeScales = {
                 "0": 0.0,
                 "1": 0.1,
                 "2": 0.25,
                 "3": 0.5,
                 "4": 0.75,
                 "5": 1.0,
                 "6": 1.5,
                 "7": 2.0,
                 "8": 3.0,
                 "9": 4.0,
                 "10": 5.0
                 };
                 document.getElementById('amplitude-scale').addEventListener('input', function(event) {
        var scale = amplitudeScales[event.target.value];
        peaksInstance.views.getView('zoomview').setAmplitudeScale(scale);
        peaksInstance.views.getView('overview').setAmplitudeScale(scale);
      }
                                                                            );
      // Segments mouse events
      peaksInstance.on('segments.dragstart', function(segment, startMarker) {
        console.log('segments.dragstart:', segment, startMarker);
      }
                      );
      peaksInstance.on('segments.dragend', function(segment, startMarker) {
        console.log('segments.dragend:', segment, startMarker);
      }
                      );
      peaksInstance.on('segments.dragged', function(segment, startMarker) {
        console.log('segments.dragged:', segment, startMarker);
        renderSegments(peaksInstance);
      }
                      );
      peaksInstance.on('segments.mouseenter', function(segment) {
        console.log('segments.mouseenter:', segment);
      }
                      );
      peaksInstance.on('segments.mouseleave', function(segment) {
        console.log('segments.mouseleave:', segment);
      }
                      );
      peaksInstance.on('segments.click', function(segment) {
        console.log('segments.click:', segment);
      }
                      );
      peaksInstance.on('zoomview.dblclick', function(time) {
        console.log('zoomview.dblclick:', time);
      }
                      );
      peaksInstance.on('overview.dblclick', function(time) {
        console.log('overview.dblclick:', time);
      }
                      );
      peaksInstance.on('player.seeked', function(time) {
        console.log('player.seeked:', time);
      }
                      );
      peaksInstance.on('player.play', function(time) {
        console.log('player.play:', time);
      }
                      );
      peaksInstance.on('player.pause', function(time) {
        console.log('player.pause:', time);
      }
                      );
      peaksInstance.on('player.ended', function() {
        console.log('player.ended');
      }
                      );
    }
    );
    }
    )(peaks);
  </script>
  <?php
endif; ?>
  <iframe name="delete-segment" style="visibility: hidden; position: absolute; left: 0; top: 0; height:0; width:0; border: none;">
  </iframe>
  </body>
</html>
