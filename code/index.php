<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>AD Demo Page</title>
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

      #demo-controls {
        margin: 0 auto 24px auto;
        width: 1000px;
        display: flex;
        align-items: center;
      }

      #demo-controls button {
        background: #fff;
        border: 1px solid #919191;
        cursor: pointer;
      }

      #audio {
        flex: 0 0 30%;
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
<?php
include ('/var/connect.php');

$dbconnect = mysqli_connect($hostname, $username, $password, $db);

if ($dbconnect->connect_error)
{
    die("Database connection failed: " . $dbconnect->connect_error);
}
echo "    <form action='?' method='post'>\n";

$episodes = mysqli_query($dbconnect, "SELECT
                                         ep_rowid,
                                         CONCAT(ep_episode_num, ' - ', ep_title) AS ep_title
                                     FROM
                                         episodes
                                     ORDER BY
                                         ep_episode_num") or die(mysqli_error($dbconnect));

echo "        <select name='id'>
            <option disabled hidden='' selected value=''>
                Choose an episode
            </option>";

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

echo "
        </select> <input name='formEpisode' type='submit' value='Select'>
    </form>\n";

if (isset($_POST['formEpisode']))
{
    $aEpisode = $_POST['id'];

    if (!isset($aEpisode))
    {
        echo ("<p>You didn't select an episode 🤔</p>\n");
    }
    else
    {

        $selected_episode = mysqli_query($dbconnect, "SELECT ep_filename, ep_episode_num, ep_release_date, ep_title, ep_description FROM episodes WHERE ep_rowid = $aEpisode") or die(mysqli_error($dbconnect));

        while ($row = mysqli_fetch_array($selected_episode))
        {
            $ep_title = htmlspecialchars("" . $row['ep_title'] . "", ENT_QUOTES);
            $ep_description = htmlspecialchars("" . $row['ep_description'] . "", ENT_QUOTES);
            echo "
    <div id='titles'>
      <h1>Dan is Testing Things</h1>
      <p>
        Using the JavaScript library <a href=\"https://github.com/bbc/peaks.js/\">Peaks.js...</a> Thanks BBC!
      </p>
      <h2>Episode {$row['ep_episode_num']}: $ep_title</h2>
      <h4>Released {$row['ep_release_date']}</h4>
      <h3>$ep_description</h3>
    </div>
    
    <div id='waveform-container'>
      <div id='zoomview-container'></div>
      <div id='overview-container'></div>
    </div>

    <div id='demo-controls'>
      <audio id='audio' controls='controls'>
        <source src='/podcasts/{$row['ep_filename']}.mp3' type='audio/mpeg'>
        Your browser does not support the audio element.
      </audio>

      <div id='controls'>
        <button data-action='zoom-in'>Zoom in</button>
        <button data-action='zoom-out'>Zoom out</button>
        <input type='text' id='seek-time' value='0.0'>
        <button data-action='seek'>Jump to (sec)</button>
        <label for='amplitude-scale'>Amplitude scale</label>
        <input type='range' id='amplitude-scale' min='0' max='10' step='1'>
        <input type='checkbox' id='auto-scroll' checked>
        <label for='auto-scroll'>Auto-scroll</label>
        <button data-action='resize'>Big/Small</button>
        <button data-action='toggle-overview'>Show/hide overview waveform</button>
      </div>
    </div>
    <div style='text-align: center;'>
        <button style='width: 50%; background-color: #4CAF50; padding: 14px 28px; font-size: 16px; cursor: pointer; text-align: center;' data-action='add-segment'>Add a Segment at current time</button><br>
        <button style='width: 50%; background-color: #4CAF50; padding: 14px 28px; font-size: 16px; cursor: pointer; text-align: center;' data-action='add-point'>Add a Point at current time</button>
    </div>
    <div class='log'>
      <div id='segments' class='hide'>
        <h2>Segments</h2>
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
              <th>Created by</th>
              <th>Comment</th>
              <th>Start time</th>
              <th>End time</th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>

      <div id='points' class='hide'>
        <h2>Points</h2>
        <table>
          <thead>
            <tr>
              <th>Created by</th>
              <th>Comment</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
    
    <script src='peaks.js'></script>
    <script>
      (function(Peaks) {
        var renderSegments = function(peaks) {
          var segmentsContainer = document.getElementById('segments');
          var segments = peaks.segments.getSegments();
          var html = '';

          for (var i = 0; i < segments.length; i++) {
            var segment = segments[i];
            var row = '<form action=\"segment.php\" id=\"segment\" target=\"delete-segment\" method=\"post\"><tr>' +
              '<td>' + segment.createdBy + '</td>' +
              '<td><textarea form=\"segment\" name=\"Comment\" rows=\"4\" cols=\"50\" maxlength=\"256\" data-action=\"update-segment-label\" data-id=\"' + segment.id + '\"/>' + segment.labelText + '</textarea></td>' +
              '<td><input form=\"segment\" name=\"StartTime\" data-action=\"update-segment-start-time\" type=\"number\" value=\"' + segment.startTime + '\" data-id=\"' + segment.id + '\"/></td>' +
              '<td><input form=\"segment\" name=\"EndTime\" data-action=\"update-segment-end-time\" type=\"number\" value=\"' + segment.endTime + '\" data-id=\"' + segment.id + '\"/></td>' +
              '<td><a href=\"#' + segment.id + '\" data-action=\"play-segment\" data-id=\"' + segment.id + '\">Play</a></td>' +
              '<td><a href=\"#' + segment.id + '\" data-action=\"loop-segment\" data-id=\"' + segment.id + '\">Loop</a></td>' +
              '<td><button form=\"segment\" name=\"Save\" value=\"' + segment.id + '\"/>Save</button> <button form=\"segment\" name=\"Delete\" value=\"' + segment.id + '\"/>Delete</button>' + '</td>' +
              '<input type=\"hidden\" form=\"segment\" name=\"EpisodeRowid\" value=\"$aEpisode\"></tr></form>';

            html += row;
          }

          segmentsContainer.querySelector('tbody').innerHTML = html;

          if (html.length) {
            segmentsContainer.classList.remove('hide');
          }

          document.querySelectorAll('input[data-action=\"update-segment-start-time\"]').forEach(function(inputElement) {
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

                segment.update({ startTime: startTime });
              }
            });
          });

          document.querySelectorAll('input[data-action=\"update-segment-end-time\"]').forEach(function(inputElement) {
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

                segment.update({ endTime: endTime });
              }
            });
          });

          document.querySelectorAll('input[data-action=\"update-segment-label\"]').forEach(function(inputElement) {
            inputElement.addEventListener('input', function(event) {
              var element = event.target;
              var id = element.getAttribute('data-id');
              var segment = peaks.segments.getSegment(id);
              var labelText = element.labelText;

              if (segment) {
                segment.update({ labelText: labelText });
              }
            });
          });
        };

        var renderPoints = function(peaks) {
          var pointsContainer = document.getElementById('points');
          var points = peaks.points.getPoints();
          var html = '';

          for (var i = 0; i < points.length; i++) {
            var point = points[i];

            var row = '<tr>' +
              '<td>' + point.id + '</td>' +
              '<td><input data-action=\"update-point-label\" type=\"text\" value=\"' + point.labelText + '\" data-id=\"' + point.id + '\"/></td>' +
              '<td><input data-action=\"update-point-time\" type=\"number\" value=\"' + point.time + '\" data-id=\"' + point.id + '\"/></td>' +
              '<td>' + '<input type=\"submit\" value=\"Save\"> <input type=\"submit\" value=\"Delete\">' + '</td>' +
              '</tr>';

            html += row;
          }

          pointsContainer.querySelector('tbody').innerHTML = html;

          if (html.length) {
            pointsContainer.classList.remove('hide');
          }

          document.querySelectorAll('input[data-action=\"update-point-time\"]').forEach(function(inputElement) {
            inputElement.addEventListener('input', function(event) {
              var element = event.target;
              var id = element.getAttribute('data-id');
              var point = peaks.points.getPoint(id);

              if (point) {
                var time = parseFloat(element.value);

                if (time < 0) {
                  time = 0;
                  element.value = 0;
                }

                point.update({ time: time });
              }
            });
          });

          document.querySelectorAll('input[data-action=\"update-point-label\"]').forEach(function(inputElement) {
            inputElement.addEventListener('input', function(event) {
              var element = event.target;
              var id = element.getAttribute('data-id');
              var point = peaks.points.getPoint(id);
              var labelText = element.labelText;

              if (point) {
                point.update({ labelText: labelText });
              }
            });
          });
        };

        var options = {
          containers: {
            zoomview: document.getElementById('zoomview-container'),
            overview: document.getElementById('overview-container')
          },
          mediaElement: document.getElementById('audio'),
          dataUri: {
            arraybuffer: 'podcasts/{$row['ep_filename']}.dat',
            json: 'podcasts/{$row['ep_filename']}.json'
          },
          keyboard: true,
          pointMarkerColor: '#006eb0',
          showPlayheadTime: true
        };

        Peaks.init(options, function(err, peaksInstance) {
          if (err) {
            console.error(err.message);
            return;
          }

          console.log(\"Peaks instance ready\");

          document.querySelector('[data-action=\"zoom-in\"]').addEventListener('click', function() {
            peaksInstance.zoom.zoomIn();
          });

          document.querySelector('[data-action=\"zoom-out\"]').addEventListener('click', function() {
            peaksInstance.zoom.zoomOut();
          });

          var segmentCounter = 1;

          document.querySelector('button[data-action=\"add-segment\"]').addEventListener('click', function() {
            peaksInstance.segments.add({
              startTime: peaksInstance.player.getCurrentTime(),
              endTime: peaksInstance.player.getCurrentTime() + 10,
              labelText: 'Segment ' + segmentCounter++,
              editable: true
            });
            renderSegments(peaksInstance);
            renderPoints(peaksInstance);
          });

          var pointCounter = 1;

          document.querySelector('button[data-action=\"add-point\"]').addEventListener('click', function() {
            peaksInstance.points.add({
              time: peaksInstance.player.getCurrentTime(),
              labelText: 'Point ' + pointCounter++,
              editable: true
            });
            renderSegments(peaksInstance);
            renderPoints(peaksInstance);
          });

          document.querySelector('button[data-action=\"seek\"]').addEventListener('click', function(event) {
            var time = document.getElementById('seek-time').value;
            var seconds = parseFloat(time);

            if (!Number.isNaN(seconds)) {
              peaksInstance.player.seek(seconds);
            }
          });

          document.getElementById('auto-scroll').addEventListener('change', function(event) {
            var view = peaksInstance.views.getView('zoomview');
            view.enableAutoScroll(event.target.checked);
          });

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
          });";
            $segments = mysqli_query($dbconnect, "SELECT
                                                      sg_rowid,
                                                      cby.us_name AS cby,
                                                      sg_cdate,
                                                      mby.us_name AS mby,
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
          });
            renderSegments(peaksInstance);
            renderPoints(peaksInstance);";
            }
            echo "
          var amplitudeScales = {
            \"0\": 0.0,
            \"1\": 0.1,
            \"2\": 0.25,
            \"3\": 0.5,
            \"4\": 0.75,
            \"5\": 1.0,
            \"6\": 1.5,
            \"7\": 2.0,
            \"8\": 3.0,
            \"9\": 4.0,
            \"10\": 5.0
          };

          document.getElementById('amplitude-scale').addEventListener('input', function(event) {
            var scale = amplitudeScales[event.target.value];

            peaksInstance.views.getView('zoomview').setAmplitudeScale(scale);
            peaksInstance.views.getView('overview').setAmplitudeScale(scale);
          });

          document.querySelector('button[data-action=\"resize\"]').addEventListener('click', function(event) {
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
          });

          document.querySelector('button[data-action=\"toggle-overview\"]').addEventListener('click', function(event) {
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
          });

          // Points mouse events

          peaksInstance.on('points.mouseenter', function(point) {
            console.log('points.mouseenter:', point);
          });

          peaksInstance.on('points.mouseleave', function(point) {
            console.log('points.mouseleave:', point);
          });

          peaksInstance.on('points.dblclick', function(point) {
            console.log('points.dblclick:', point);
          });

          peaksInstance.on('points.dragstart', function(point) {
            console.log('points.dragstart:', point);
          });

          peaksInstance.on('points.dragmove', function(point) {
            console.log('points.dragmove:', point);
            renderSegments(peaksInstance);
            renderPoints(peaksInstance);
          });

          peaksInstance.on('points.dragend', function(point) {
            console.log('points.dragend:', point);
          });

          // Segments mouse events

          peaksInstance.on('segments.dragstart', function(segment, startMarker) {
            console.log('segments.dragstart:', segment, startMarker);
          });

          peaksInstance.on('segments.dragend', function(segment, startMarker) {
            console.log('segments.dragend:', segment, startMarker);
          });

          peaksInstance.on('segments.dragged', function(segment, startMarker) {
            console.log('segments.dragged:', segment, startMarker);
            renderSegments(peaksInstance);
            renderPoints(peaksInstance);
          });

          peaksInstance.on('segments.mouseenter', function(segment) {
            console.log('segments.mouseenter:', segment);
          });

          peaksInstance.on('segments.mouseleave', function(segment) {
            console.log('segments.mouseleave:', segment);
          });

          peaksInstance.on('segments.click', function(segment) {
            console.log('segments.click:', segment);
          });

          peaksInstance.on('zoomview.dblclick', function(time) {
            console.log('zoomview.dblclick:', time);
          });

          peaksInstance.on('overview.dblclick', function(time) {
            console.log('overview.dblclick:', time);
          });

          peaksInstance.on('player.seeked', function(time) {
            console.log('player.seeked:', time);
          });

          peaksInstance.on('player.play', function(time) {
            console.log('player.play:', time);
          });

          peaksInstance.on('player.pause', function(time) {
            console.log('player.pause:', time);
          });

          peaksInstance.on('player.ended', function() {
            console.log('player.ended');
          });
        });
      })(peaks);
    </script>
    ";

        }

    }
}
?>
<iframe name="delete-segment" style="visibility: hidden; position: absolute; left: 0; top: 0; height:0; width:0; border: none;"></iframe>
</body>
</html>
