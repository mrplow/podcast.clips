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
    <style>
      body {
        font-family: 'Helvetica neue', Helvetica, Arial, sans-serif;
      }
      #titles, #waveform-container {
        margin: 24px auto;
        width: 1000px;
      }
      #zoomview-container, #overview-container {
        margin: 0 0 24px 0;
        line-height: 0;
        -moz-box-shadow: 3px 3px 20px #919191;
        -webkit-box-shadow: 3px 3px 20px #919191;
        box-shadow: 3px 3px 20px #919191;
      }
      #zoomview-container {
        height: 200px;
      }
      #overview-container {
        height: 85px;
      }
      #media-controls {
        margin: 0 auto 24px auto;
        width: 1000px;
        display: flex;
        align-items: center;
        flex: 0 0 100%;
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
      .segtime {
        padding: 16px 16px;
        margin: 4px 2px;
        font-size: 15px;
        width: 62px;
      }
      #audio {
        flex: 0 0 100%;
      }
      #controls {
        flex: 1;
        margin-left: 1em;
      }
      #seek-time {
        width: 4em;
      }
      .log {
        margin: 0 auto 24px auto;
        width: 1000px;
      }
      table {
        width: 100%;
      }
      table th {
        text-align: left;
      }
      table th, table td {
        padding: 0.5em;
      }
      .hide {
        display: none;
      }
    </style>
  </head>
  <body>
    <form action='?' method='post'>
      <select name='id'>
        <option disabled hidden='' selected value=''>
          Choose an episode
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
      <input name='formEpisode' type='submit' value='Select'>
    </form>Logged in as 
    <?php echo $_SESSION['user_name'];?>
    <br />
    <a href="/logout.php">Logout
    </a>
    <br />
    <a href="/changepass.php">Change Password
    </a>
    <?php if (isset($_POST['formEpisode'])): ?>
    <?php $aEpisode = $_POST['id']; ?>
    <?php if (isset($aEpisode)): ?>
    <?php
$selected_episode = $dbconnect->prepare("SELECT ep_filename, ep_episode_num, ep_release_date, ep_title, ep_description FROM episodes WHERE ep_rowid = ?");
$selected_episode->bind_param('i', $aEpisode);
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
    <div id='titles'>
      <h1>Dan is Testing Things
      </h1>
      <p>
        Using the JavaScript library 
        <a href="https://github.com/bbc/peaks.js/">Peaks.js...
        </a> Thanks BBC!
      </p>
      <h2>Episode 
        <?php echo $ep_episode_num . ": " . $ep_title;?>
      </h2>
      <h4>Released 
        <?php echo $ep_release_date;?>
      </h4>
      <h3>
        <?php echo $ep_description;?>
      </h3>
    </div>
    <div id='waveform-container'>
      <div id='zoomview-container'>
      </div>
      <div id='overview-container'>
      </div>
    </div>
    <div id='media-controls'>
      <audio id='audio' controls='controls'>
        <source src='/podcasts/<?php echo $ep_filename;?>.mp3' type='audio/mpeg'>
        Your browser does not support the audio element.
      </audio>
    </div>
    <div id='media-controls'>
      <button data-action='zoom-in'>Zoom in
      </button>
      <button data-action='zoom-out'>Zoom out
      </button>
      <label for='amplitude-scale'>&nbsp; Amplitude scale
      </label>
      <input type='range' id='amplitude-scale' min='0' max='10' step='1'>
    </div>
    <div id='media-controls'>
      <input type='text' id='seek-time' value='0.0'>
      <button data-action='seek'>Jump to (sec)
      </button>
      <input type='checkbox' id='auto-scroll' checked>
      <label for='auto-scroll'>Auto-scroll
      </label>
    </div>
    <div id='media-controls'>
      <button data-action='resize'>Big/Small
      </button>
      <button data-action='toggle-overview'>Show/hide overview waveform
      </button>
    </div>
    <div id='media-controls'>
      <button style='font-size: 24px;' data-action='add-segment'>Add a Segment at current time
      </button>
      <br>
    </div>
    <div class='log'>
      <div id='segments' class='hide'>
        <h2>Segments
        </h2>
        <table>
          <colgroup>
            <col span='1'>
            <col span='1' style='width: 60%;'>
            <col span='1'>
            <col span='1'>
            <col span='1'>
            <col span='1'>
            <col span='1'>
          </colgroup>
          <thead>
            <tr>
              <th>Created by
              </th>
              <th>Comment
              </th>
              <th>Start time
              </th>
              <th>End time
              </th>
              <th>
              </th>
              <th>
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
            var row = '<form action="segment.php" id="segment" target="delete-segment" method="post"><tr>' +
                '<td>' + segment.createdBy + '</td>' +
                '<td><textarea form="segment" name="Comment" rows="2" cols="30" maxlength="256" data-action="update-segment-label" data-id="' + segment.id + '"/>' + segment.labelText + '</textarea></td>' +
                '<td><input form="segment" class="segtime" name="StartTime" data-action="update-segment-start-time" type="number" value="' + segment.startTime + '" data-id="' + segment.id + '"/></td>' +
                '<td><input form="segment" class="segtime" name="EndTime" data-action="update-segment-end-time" type="number" value="' + segment.endTime + '" data-id="' + segment.id + '"/></td>' +
                '<td><a href="#' + segment.id + '" data-action="play-segment" data-id="' + segment.id + '">Play</a></td>' +
                '<td><a href="#' + segment.id + '" data-action="loop-segment" data-id="' + segment.id + '">Loop</a></td>' +
                '<td><button form="segment" name="Save" value="' + segment.id + '"/>Save</button>' +
                '<div id="notsaved">' +
                '  <button form="segment" name="Delete" value="' + segment.id + '"/>Delete</button>' +
                '  <button form="segment" name="Export" value="' + segment.id + '"/>Export</button>' +
                '</div></td>' +
                '<input type="hidden" form="segment" name="EpisodeRowid" value="<?php echo $aEpisode;?>">' +
                '<input type="hidden" form="segment" name="ExportStartTime" value="' + segment.startTime + '">' +
                '<input type="hidden" form="segment" name="ExportEndTime" value="' + segment.endTime + '">' +
                '<input type="hidden" form="segment" name="EpisodeFilename" value="<?php echo $ep_filename;?>">' +
                '<input type="hidden" form="segment" name="UserRowid" value="' + segment.createdBy + '"></tr></form>';
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
            arraybuffer: 'podcasts/<?php echo $ep_filename;?>.dat',
            json: 'podcasts/<?php echo $ep_filename;?>.json'
          }
          ,
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
              endTime: peaksInstance.player.getCurrentTime() + 10,
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
                                   sg_mby = cby.us_rowid
                                   WHERE
                                   sg_rowid_episode = $aEpisode") or die(mysqli_error($dbconnect));
                                   while ($row = mysqli_fetch_array($segments))
          {
            unset($sg_rowid, $cby, $sg_cdate, $mby, $sg_mdate, $sg_comment, $sg_starttime, $sg_endtime);
            $sg_rowid = $row['sg_rowid'];
            $cby = htmlspecialchars("" . $row['cby'] . "", ENT_QUOTES);
            $sg_cdate = $row['sg_cdate'];
            $mby = htmlspecialchars("" . $row['mby'] . "", ENT_QUOTES);
            $sg_mdate = $row['sg_mdate'];
            $sg_comment = str_replace("\n", "&#010;", str_replace("\r", "&#010;", str_replace("\r\n", "&#010;", htmlspecialchars("" . $row['sg_comment'] . "", ENT_QUOTES))));
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
        document.querySelector('button[data-action="resize"]').addEventListener('click', function(event) {
          var zoomviewContainer = document.getElementById('zoomview-container');
          var overviewContainer = document.getElementById('overview-container');
          var zoomviewStyle = zoomviewContainer.offsetHeight === 200 ? 'height:300px' : 'height:200px';
          var overviewStyle = overviewContainer.offsetHeight === 85  ? 'height:200px' : 'height:85px';
          zoomviewContainer.setAttribute('style', zoomviewStyle);
          overviewContainer.setAttribute('style', overviewStyle);
          var zoomview = peaksInstance.views.getView('zoomview');
          if (zoomview) {
            zoomview.fitToContainer();
          }
          var overview = peaksInstance.views.getView('overview');
          if (overview) {
            overview.fitToContainer();
          }
        }
                                                                               );
        document.querySelector('button[data-action="toggle-overview"]').addEventListener('click', function(event) {
          var container = document.getElementById('overview-container');
          var overview = peaksInstance.views.getView('overview');
          if (overview) {
            peaksInstance.views.destroyOverview();
            container.style.display = 'none';
          }
          else {
            container.style.display = 'block';
            peaksInstance.views.createOverview(container);
          }
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
else: ?>
    <p>You didn't select an episode 🤔
    </p>
    <?php
endif; ?>
    <?php
endif; ?>
    <iframe name="delete-segment" style="visibility: hidden; position: absolute; left: 0; top: 0; height:0; width:0; border: none;">
    </iframe>
  </body>
</html>
