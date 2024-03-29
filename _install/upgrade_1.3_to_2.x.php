#!/usr/bin/php
<?php

if (php_sapi_name() != 'cli') {
	echo "Please run in CLI mode\n";
	exit(1);
}

require_once __DIR__ . '/../includes/database.inc.php';
verbinden();

$cont = file_get_contents(__DIR__ . '/upgrade_1.3_to_2.x.sql');

$queries = preg_split('@;\s*\n@ismU', $cont);

foreach ($queries as $query) {
	$query = trim($query);
	if ($query == '') continue;
	if (substr($query, 0, 2) == '--') continue;
	$query .= ';';

	db_query($query);

	$err = db_error();
	if ($err) {
		fwrite(STDERR, "mySQL error $err at query $query\n\n");
	}
}
