<?php

function verbinden() {
	global $mysql_database, $mysql_server, $mysql_pass, $mysql_user;
	global $link2;

	$link2 = mysql_connect($mysql_server, $mysql_user, $mysql_pass);
	if (!$link2) {
		die('<b>Verbindung zum MySQL-Server konnte nicht hergestellt werden! ('.mysql_error().')</b>');
	}

	if (!mysql_select_db($mysql_database)) {
		die('<b>Verbindung zum MySQL-Server konnte nicht hergestellt werden! ('.mysql_error().')</b>');
	}

	register_shutdown_function('trennen');
	mysql_select_db($mysql_database);
}

function trennen() {
	global $link2;
	@mysql_close($link2);
}

?>
