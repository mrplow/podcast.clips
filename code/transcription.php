<?php
session_start();
if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
}

if (isset($_SESSION['user_validated']))
{
    $LastValidated  = new DateTime($_SESSION['user_validated']);
    $CurrentTime    = new DateTime('now');
    $SinceValidated = $LastValidated->diff($CurrentTime);
    $DaysSince = $SinceValidated->format('%a');
    if ($DaysSince > 32)
    {
        header("Location: /validate.php");
    }
}
else
{
    header("Location: /validate.php");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>The After Disaster Podcast Clips
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" crossorigin="anonymous">
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
  </head>
  <body>
<div class="container">
      <div class="text-right">
        Logged in as
        <?php echo " " . $_SESSION['user_name']; ?>
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
        <?php if ($_SESSION['user_level'] <= 10)
{
    echo "<br /><a href=\"/upload.php\">Upload episode</a>";
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
      </div>
</div>
<div class="container">
  <h2>Transcriptions</h2>

        <div >
            <table id="TranscriptionTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>rowid</th>
                        <th>Episode #</th>
                        <th>Title</th>
                        <th>Release Date</th>
                        <th>Timestamp</th>
                        <th>Transcription</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>rowid</th>
                        <th>Episode #</th>
                        <th>Title</th>
                        <th>Release Date</th>
                        <th>Timestamp</th>
                        <th>Transcription</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <script>
            $(document).ready(function() {
                $('#TranscriptionTable').DataTable( {
                    "processing": true,
                    "serverSide": true,
                    "ajax": "ajax_transcription.php",
                    "columnDefs": [
                        {
                            "targets": [ 0 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 1 ],
                            "searchable": false,
                            "render": function( data, type, row )
                                {
                                    return row[1].replace(".0", "")
                                }
                        },
                        {
                            "targets": [ 2 ],
                            "searchable": false
                        },
                        {
                            "targets": [ 3 ],
                            "searchable": false
                        },
                        {
                            "targets": [ 4 ],
                            "searchable": false,
                            "render": function( data, type, row )
                                {
                                    return '<a href="/index.php?epid='+ row[0] +'&timestamp='+data+'" target="_blank">'+data+'</a>'
                                }
                        },
                        {
                            "targets": [ 5 ],
                            "searchable": true
                        }
                    ]
                } );
            } );
        </script>
    </div>
</body>
</html>
