<?php

function verbinden() {
	global $mysql_database, $mysql_server, $mysql_pass, $mysql_user;

	if (!db_connect($mysql_server, $mysql_user, $mysql_pass)) {
		die('<b>Verbindung zum MySQL-Server konnte nicht hergestellt werden! ('.db_error().')</b>');
	}

	if (!db_select_db($mysql_database)) {
		die('<b>Verbindung zum MySQL-Server konnte nicht hergestellt werden! ('.db_error().')</b>');
	}

	register_shutdown_function('trennen');
	db_select_db($mysql_database);
}

function trennen() {
	@db_close();
}

// Liefert die Anzahl der Zeilen im Ergebnis
function db_num_rows($result) {
	if (!$result) {
		$err = db_error();
		throw new Exception("Called db_num_rows() with an erroneous argument.".($err == '' ? '' : " Possible cause: $err"));
	}
	return $result->num_rows;
}

// Liefert eine Ergebniszeile als Objekt
function db_fetch_object($result, $class_name="stdClass", $params=null) {
	if (!$result) {
		$err = db_error();
		throw new Exception("Called db_fetch_object() with an erroneous argument.".($err == '' ? '' : " Possible cause: $err"));
	}
	if ($params) {
		return $result->fetch_object($class_name, $params);
	} else {
		return $result->fetch_object($class_name);
	}
}

// Öffnet eine Verbindung zu einem MySQL-Server
function db_connect($server=null, $username=null, $password=null, $new_link=false, $client_flags=0) {
	global $vts_mysqli;
        $ary = explode(':', $server);
	$host = $ary[0];
	$ini_port = ini_get("mysqli.default_port");
	$port = isset($ary[1]) ? (int)$ary[1] : ($ini_port ? (int)$ini_port : 3306);
	if (is_null($server)) $port = ini_get("mysqli.default_host");
	if (is_null($username)) $port = ini_get("mysqli.default_user");
	if (is_null($password)) $port = ini_get("mysqli.default_password");
	$vts_mysqli = new mysqli($host, $username, $password, /*dbname*/'', $port, ini_get("mysqli.default_socket"));
	return (empty($vts_mysqli->connect_error) && ($vts_mysqli->connect_errno == 0)) ? $vts_mysqli : false;
}

// Schließt eine Verbindung zu MySQL
function db_close($link_identifier=NULL) {
	global $vts_mysqli;
	$li = is_null($link_identifier) ? $vts_mysqli : $link_identifier;
	if (is_null($li)) throw new Exception("Cannot execute db_close(). No valid connection to server.");

	return $li->close();
}

// Liefert den Fehlertext der zuvor ausgeführten MySQL Operation
function db_error($link_identifier=NULL) {
	global $vts_mysqli;
	$li = is_null($link_identifier) ? $vts_mysqli : $link_identifier;
	if (is_null($li)) throw new Exception("Cannot execute db_error(). No valid connection to server.");

	return !empty($li->connect_error) ? $li->connect_error : $li->error;
}

// Maskiert spezielle Zeichen innerhalb eines Strings für die Verwendung in einer SQL-Anweisung
function db_real_escape_string($unescaped_string, $link_identifier=NULL) {
	global $vts_mysqli;
	$li = is_null($link_identifier) ? $vts_mysqli : $link_identifier;
	if (is_null($li)) throw new Exception("Cannot execute db_real_escape_string(). No valid connection to server.");

	return $li->escape_string($unescaped_string);
}

// Sendet eine Anfrage an MySQL
function db_query($query, $link_identifier=NULL) {
	global $vts_mysqli;
	$li = is_null($link_identifier) ? $vts_mysqli : $link_identifier;
	if (is_null($li)) throw new Exception("Cannot execute db_query(). No valid connection to server.");

	return $li->query($query, $resultmode=MYSQLI_STORE_RESULT);
}

// Auswahl einer MySQL Datenbank
function db_select_db($database_name, $link_identifier=NULL) {
	global $vts_mysqli;
	$li = is_null($link_identifier) ? $vts_mysqli : $link_identifier;
	if (is_null($li)) throw new Exception("Cannot execute db_select_db(). No valid connection to server.");

	return $li->select_db($database_name);
}

// Liefert die ID, die in der vorherigen Abfrage erzeugt wurde
function db_insert_id($link_identifier=NULL) {
	global $vts_mysqli;
	$li = is_null($link_identifier) ? $vts_mysqli : $link_identifier;
	if (is_null($li)) throw new Exception("Cannot execute db_insert_id(). No valid connection to server.");

	return $li->insert_id;
}
