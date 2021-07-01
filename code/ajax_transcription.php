<?php
include ('/var/connect.php');

$table = 'episode_transcriptions';

$primaryKey = 'tr_rowid';

$columns = array (
		array (
				'db' => 'ep_rowid',
				'dt' => 0
		),
		array (
				'db' => 'ep_episode_num',
				'dt' => 1
		),
		array (
				'db' => 'ep_title',
				'dt' => 2
		),
		array (
				'db' => 'ep_release_date',
				'dt' => 3
		),
		array (
				'db' => 'hhmmss',
				'dt' => 4
		),
		array (
				'db' => 'tr_text',
				'dt' => 5
		)
);

$sql_details = array (
		'user' => $GLOBALS ["mysql_username"],
		'pass' => $GLOBALS ["mysql_password"],
		'db' => $GLOBALS ["mysql_database"],
		'host' => $GLOBALS ["mysql_hostname"]
);

require ('ssp.class.php');

echo json_encode ( SSP::simple ( $_GET, $sql_details, $table, $primaryKey, $columns ) );
