<?php

# ViaThinkSoft PHP Guestbook 2.8.3
# (C) 2003-2023 ViaThinkSoft, Daniel Marschall
# Licensed under the Apache 2.0 License

// Version des Gästebuchs
$version = '2.8.2';

// START DEFAULT WERTE

$charset = 'ISO-8859-1';

// Der Titel der Seite
$seitentitel = 'Mein Gästebuch';

// Seitenkopf
$seitenkopf = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
        <meta http-equiv="Content-Type" content="text/html; charset={CHARSET}" />
	<title>'.htmlentities($seitentitel).' G&auml;stebuch</title>
</head>

<body>';

// Seitenfuß
$seitenfuss = '</body></html>';

// Farben
$farbe1 = '#505080';	// Rand eines Eintrags
$farbe2 = '#D2DAF0';	// Eintrag Segment 2 (Text) BG
$farbe3 = '#A0B1E0';	// Eintrag Segment 1 (Kopfzeile) BG
$farbe4 = '#333333';	// Erstellungsdatum Schrift
$farbe5 = '#E2E7F5';	// Eintrag Segment 3 (Admin-Kommentar, optional) BG
$farbe6 = 'red';	// Fehlermeldung
$farbe7 = 'blue';	// Pflichtfeld-Stern
$farbe8 = 'green';	// Erfolgsmeldung
$farbe9  = 'black';	// Segment 1 (Kopfzeile) Text
$farbe10 = 'black';	// Segment 2 (Text) Text
$farbe11 = 'black';	// Segment 3 (Admin-Kommentar, optional) Text

// Die MySQL-Zugangsdaten
$mysql_server   = 'localhost';
$mysql_user     = 'root';
$mysql_pass     = '';
$mysql_database = 'guestbook';

// Die Datenbanktabellennamen
$table_entries = 'gaestebuch_entries';
$table_smileys = 'gaestebuch_smileys';

// E-Mail-Adresse
$adminmail = 'your_email_address@example.com';
$adminmail_cc = '';

// Einträge pro Seite
$eintraege_proseite = 10;

// Vorsicht: Der Server muss autorisiert sein, eine E-Mail zu über diese Domain zu senden (SPF/DKIM)
$cfg_from_email = 'noreply@example.com';

// Features
$cfg_feature_simple_antispam   = true;
$cfg_automatisch_freischalten  = false;
$cfg_unfreigeschaltete_anzegen = false;
$cfg_vorschau                  = true;

// Recaptcha - This is the most secure Captcha
// It also helps against "F5" spamming!
// Get a FREE API key here: https://www.google.com/recaptcha/admin/create
$cfg_recaptcha_enabled = false;
$cfg_recaptcha_pubkey  = '';
$cfg_recaptcha_privkey = '';

// see https://daniel-lange.com/archives/66-ICQ-web-status-icons.html
$cfg_icq_statusicon = 5;

// ENDE DEFAULT WERTE

if (!file_exists(__DIR__ . '/config/config.inc.php')) {
	die('ERROR: File <b>config/config.inc.php</b> does not exist. Please create it using <b>config/config.original.inc.php</b>');
}
require_once __DIR__ . '/config/config.inc.php';

if ($cfg_recaptcha_enabled) $cfg_feature_simple_antispam = false;

require_once __DIR__ . '/includes/database.inc.php';
verbinden();

require_once __DIR__ . '/includes/SecureMailer.class.php';
require_once __DIR__ . '/includes/ip_functions.inc.php';

if ($cfg_recaptcha_enabled) {
	require_once __DIR__ . '/includes/recaptcha/src/autoload.php';
}

# ------------------------------

// Funktion zum sichern von E-Mail-Adressen
// $crypt_linktext = 0
// --> geeignet für <img>-links, da $linktext nicht in ASCII übersetzt wird!
// $crypt_linktext = 1
// --> geeignet für text-links für höhere Sicherheit

function secure_email($email, $linktext, $crypt_linktext) {
	$aus = '';
	if ($email != '') {
		$aus .= '<script language="JavaScript" type="text/javascript">
<!--
	document.write("<a href=\"");'."\n";

		$gesamttext = 'mailto:'.$email;
		for ($i=0; $i<strlen($gesamttext); $i++) {
			$aus .= '  document.write("&#'.ord(substr($gesamttext, $i, 1)).';");'."\n";
		}

		$aus .= '  document.write("\">");'."\n";

		if ($crypt_linktext == '1') {
			$gesamttext = $linktext;
			for ($i=0; $i<strlen($gesamttext); $i++) {
				$aus .= '  document.write("&#'.ord(substr($gesamttext, $i, 1)).';");'."\n";
			}
		} else {
			$gesamttext = str_replace('"', '\"', $linktext);
			$aus .= '  document.write("'.$gesamttext.'");';
		}

		$aus .= '  document.write("<\/a>");
// -->
</script>';
	}

	return $aus;
}

function myhtmlentities($nachricht) {
	global $charset;
	return htmlentities($nachricht, ENT_COMPAT | ENT_XHTML, $charset);
}

function parse_html($nachricht, $loc_dir = '') {
	global $table_smileys;

	// Smiley pre-parsing
	$uid = uniqid();
	$result = db_query("SELECT `zeichen`, `image`, `beschreibung`, `id` FROM `".db_real_escape_string($table_smileys)."` WHERE `enabled` = '1' ORDER BY `id` ASC");
	while ($row = db_fetch_object($result)) {
		# $nachricht = str_replace($row->zeichen, '<img src="images/smileys/'.$row->image.'" alt="'.myhtmlentities($row->beschreibung).'" title="'.myhtmlentities($row->beschreibung).'" />', $nachricht);
		$nachricht = str_replace($row->zeichen, "\nSMILEY{$uid}:".$row->id.":{$uid}YELIMS\n", $nachricht);
	}

	// HTML Parsing
	$nachricht = myhtmlentities($nachricht);
# Damit funktioniert wordwrap() nicht gescheit, was für den Mailverkehr wichtig ist. außerdem gibt es dann keinen umbruch in der voransicht
#	$nachricht = str_replace(' ', '&nbsp;', $nachricht);
# TODO: man sollte ' '->&nbsp; erst ersetzen, wenn es mehr als 1 leerzeichen ist
	$nachricht = nl2br($nachricht);
	$nachricht = str_replace('<br>', '<br />', $nachricht); // to be sure
	$nachricht = str_replace('<BR>', '<br />', $nachricht); // to be sure

	// Linkanalyse von phpBB
	$nachricht = ' ' . $nachricht;
	$nachricht = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3" target="_blank">\2://\3</a>', $nachricht);
	$nachricht = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3" target="_blank">\2.\3</a>', $nachricht);
	$nachricht = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $nachricht);
	$nachricht = substr($nachricht, 1);

	// Final smiley parsing
	$result = db_query("SELECT `zeichen`, `image`, `beschreibung`, `id` FROM `".db_real_escape_string($table_smileys)."` WHERE `enabled` = '1' ORDER BY `id` ASC");
	while ($row = db_fetch_object($result)) {
		$nachricht = str_replace("<br />\nSMILEY{$uid}:".$row->id.":{$uid}YELIMS<br />\n", '<img src="'.$loc_dir.'images/smileys/'.$row->image.'" alt="'.myhtmlentities($row->beschreibung).'" title="'.myhtmlentities($row->beschreibung).'" />', $nachricht);
	}

	return $nachricht;
}

function anznachricht($name, $ort, $email, $home, $icq, $nachricht, $kommentar, $zeit, $datum) {
	global $farbe1, $farbe2, $farbe3, $farbe4, $farbe5, $farbe6, $farbe7, $farbe8, $farbe9, $farbe10, $farbe11, $this_dir, $cfg_icq_statusicon;

	$zzeit = explode(":", $zeit);
	$zzeit = $zzeit[0].".".$zzeit[1];
	$ddatum = explode("-", $datum);
	$ddatum = $ddatum[2].".".$ddatum[1].".".$ddatum[0];
	$nachricht = parse_html($nachricht, $this_dir);

	echo '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-color:'.$farbe1.';border-collapse:collapse">';
	echo '  <tr>';
	echo '    <td width="100%" bgcolor="'.$farbe3.'" style="border-color:'.$farbe1.';border-collapse:collapse">';
	echo '    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="color:'.$farbe9.'">';
	echo '      <tr>';
	echo '        <td width="75%" align="left"><b>'.myhtmlentities($name).'</b>';
	if ($ort != '') echo ' aus '.myhtmlentities($ort);
	echo '</td>';
	echo '        <td width="5%">&nbsp;</td>';
	echo '        <td width="20%" align="right">';
	if ($email != '') {
		echo secure_email(myhtmlentities($email), '<img src="images/email.gif" border="0" height="18" width="17" alt="E-Mail-Adresse" title="E-Mail-Adresse" />', 0);
	}
	if ($home != '') {
		echo '	    <a href="'.myhtmlentities($home).'" target="_blank"><img src="images/homepage.gif" border="0" height="18" width="16" alt="Homepage" title="Homepage" /></a>';
	}
	if ($icq != '') {
		echo '	    <a href="https://icq.com/people/'.urlencode($icq).'" target="_blank"><img src="https://status.icq.com/online.gif?icq='.urlencode($icq).'&amp;img='.$cfg_icq_statusicon.'" alt="Mein ICQ Status" title="Mein ICQ Status" border="0"></a></a>';
	}
	echo '</td></tr></table></td></tr>';
	echo '<tr><td width="100%" bgcolor="'.$farbe2.'" style="color:'.$farbe10.'" align="left">'.$nachricht.'<br /><br /><font size="2" color="'.$farbe4.'">Dieser Eintrag wurde am '.$ddatum.' um '.$zzeit.' Uhr erstellt.</font></td></tr>';
	if ($kommentar != '') {
		$kommentar = parse_html($kommentar, $this_dir);
		echo '<tr><td width="100%" bgcolor="'.$farbe5.'" style="color:'.$farbe11.'" align="left"><b>Kommentar des Seiteneigent&uuml;mers:</b><br /><br />'.$kommentar.'</td></tr>';
	}
	echo '</table><br />';
}

# --------------------------

# http://stackoverflow.com/questions/1175096/how-to-find-out-if-you-are-using-https-without-serverhttps
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443);

$proto = $is_https ? 'https' : 'http';
$inphp = $proto.'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$this_dir = dirname($inphp).'/';

$seitenkopf = str_replace('{CHARSET}', $charset, $seitenkopf);
echo $seitenkopf;

// Vor der Vorschau alles prüfen
$err_name      = false;
$err_nachricht = false;
$err_icq       = false;
$err_email     = false;
$err_homepage  = false;
$err_antispam  = false;
$relfehler     = '';

$view_vorschau     = isset($_REQUEST['action']) && ($_REQUEST['action'] == 'vorschau');
$view_abschicken   = isset($_REQUEST['action']) && ($_REQUEST['action'] == 'abschicken');
$view_eintrag      = isset($_REQUEST['action']) && ($_REQUEST['action'] == 'eintrag');
$view_freischalten = isset($_REQUEST['action']) && ($_REQUEST['action'] == 'freischalten');

$name      = (isset($_POST['name'])      ? trim($_POST['name'])      : '');
$ort       = (isset($_POST['ort'])       ? trim($_POST['ort'])       : '');
$email     = (isset($_POST['email'])     ? trim($_POST['email'])     : '');
$homepage  = (isset($_POST['homepage'])  ? trim($_POST['homepage'])  : '');
$icq       = (isset($_POST['icq'])       ? trim($_POST['icq'])       : '');
$nachricht = (isset($_POST['nachricht']) ? trim($_POST['nachricht']) : '');
$antispam  = (isset($_POST['antispam'])  ? trim($_POST['antispam'])  : '');
$kommentar = '';

$icq = str_replace('-', '', $icq); // XXX-XXX-XXX -> XXXXXXXXX

# -----------------------------------

function md5_valid($id, $md5_message) {
	global $table_entries;
	return md5($table_entries.'-'.$id.'-'.$md5_message.'-GBINT');
}

# -----------------------------------

if ($view_freischalten) {
	$id  = isset($_REQUEST['id'])  ? $_REQUEST['id']  : '';
	$md5 = isset($_REQUEST['md5']) ? $_REQUEST['md5'] : '';

	echo '<h1>G&auml;stebucheintrag freischalten</h1>';

	if (($id == '') || ($md5 == '')) {
		die('<p><font color="'.$farbe6.'">Ein Fehler ist aufgetreten. Fehler in den Parametern.</font></p>'.$seitenfuss);
	}

	$result = db_query("SELECT `show`, MD5(`nachricht`) AS `md5` FROM `".db_real_escape_string($table_entries)."` WHERE `id` = '".db_real_escape_string($id)."'");
	if ($row = db_fetch_object($result)) {
		if ($row->show == 1) {
			echo '<p><font color="'.$farbe8.'">Eintrag ist bereits freigeschaltet!</font></p>';
		} else {
			$md5_valid = md5_valid($id, $row->md5);
			if (strtolower($md5) == strtolower($md5_valid)) {
				db_query("UPDATE `".db_real_escape_string($table_entries)."` SET `show` = '1' WHERE `id` = '".db_real_escape_string($id)."'");
				echo '<p><font color="'.$farbe8.'">Eintrag erfolgreich freigeschaltet!</font></p>';
			} else {
				echo '<p><font color="'.$farbe6.'">Keine Berechtigung, den Eintrag freizuschalten!</font></p>';
			}
		}
	}

	die($seitenfuss);
}

if ($cfg_feature_simple_antispam) {
	// NG: erster block der IP adresse hinzufügen
	$antispam_awaiting = (date('d')-15) * (2+date('m')) * 2 + 1337 + date('Y');
}

if (($view_vorschau) || ($view_abschicken)) {
	// Name prüfen
	if ($name == '') $err_name = true;

	// Nachricht prüfen
	if ($nachricht == '') $err_nachricht = true;

	// ICQ prüfen, wenn angegeben
	if ((!preg_match('/^[0-9]+$/', $icq)) && ($icq != '')) $err_icq = true;

	// E-Mail-Adresse prüfen, wenn angegeben
	if ((!preg_match('/^[a-z0-9\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is', $email)) && ($email != '')) $err_email = true;

	// Homepage prüfen, wenn angegeben
	if ($homepage != '') {
		if (!preg_match('#^http[s]?:\/\/#i', $homepage)) {
			$homepage = 'http://' . $homepage;
		}
		if (!preg_match('#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $homepage)) {
			$err_homepage = true;
		}
	}

	// AntiSpam prüfen
	if (($cfg_feature_simple_antispam) && ($antispam != $antispam_awaiting)) $err_antispam = true;

	// Fehler?
	if (($err_name) || ($err_nachricht) || ($err_icq) || ($err_email) || ($err_homepage) || ($err_antispam)) {
		$relfehler = '<font color="'.$farbe6.'"><u>Fehler</u>: Es wurden nicht alle Pflichtfelder ausgef&uuml;llt oder einige Felder enthalten einen Fehler!</font>';
	}

	if (!$relfehler) {
		$datum = date('Y-m-d');
		$zeit  = date('H:i:s');
		$ip    = get_real_ip(); // $_SERVER['REMOTE_ADDR'];
		$host  = gethostbyaddr($ip);
		$whois_service = "https://whois.viathinksoft.de/whois/$ip";

		if ($view_vorschau) {
			echo '<h1>G&auml;stebucheintrag Vorschau</h1>';
			echo '<div align="center">';

			anznachricht($name, $ort, $email, $homepage, $icq, $nachricht, $kommentar, $zeit, $datum);

			echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr>\n";

			echo "<td>\n";
			echo "<form action=\"$inphp\" method=\"post\" name=\"frm2\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"eintrag\" />\n";
			echo "<input type=\"hidden\" name=\"name\" value=\"".myhtmlentities($name)."\" />\n";
			echo "<input type=\"hidden\" name=\"ort\" value=\"".myhtmlentities($ort)."\" />\n";
			echo "<input type=\"hidden\" name=\"email\" value=\"".myhtmlentities($email)."\" />\n";
			echo "<input type=\"hidden\" name=\"homepage\" value=\"".myhtmlentities($homepage)."\" />\n";
			echo "<input type=\"hidden\" name=\"icq\" value=\"".myhtmlentities($icq)."\" />\n";
			echo "<input type=\"hidden\" name=\"nachricht\" value=\"".myhtmlentities($nachricht)."\" />\n";
			if ($cfg_feature_simple_antispam) echo "<input type=\"hidden\" name=\"antispam\" value=\"".myhtmlentities($antispam)."\" />\n";
			echo "<a href=\"javascript:document.frm2.submit()\"><img src=\"images/buttons/aendern.gif\" border=\"0\" height=\"31\" width=\"146\" alt=\"&Auml;ndern\" title=\"&Auml;ndern\" /></a>";
			echo "</form>";
			echo "</td>";

			echo "<td><img src=\"images/spacer.gif\" height=\"1\" width=\"30\" alt=\"\" /></td>";

			echo "<td>\n";
			echo "<form action=\"$inphp\" method=\"post\" name=\"frm1\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"abschicken\" />\n";
			echo "<input type=\"hidden\" name=\"name\" value=\"".myhtmlentities($name)."\" />\n";
			echo "<input type=\"hidden\" name=\"ort\" value=\"".myhtmlentities($ort)."\" />\n";
			echo "<input type=\"hidden\" name=\"email\" value=\"".myhtmlentities($email)."\" />\n";
			echo "<input type=\"hidden\" name=\"homepage\" value=\"".myhtmlentities($homepage)."\" />\n";
			echo "<input type=\"hidden\" name=\"icq\" value=\"".myhtmlentities($icq)."\" />\n";
			echo "<input type=\"hidden\" name=\"nachricht\" value=\"".myhtmlentities($nachricht)."\" />\n";
			if ($cfg_feature_simple_antispam) echo "<input type=\"hidden\" name=\"antispam\" value=\"".myhtmlentities($antispam)."\" />\n";
			echo "<a href=\"javascript:document.frm1.submit()\"><img src=\"images/buttons/abschicken.gif\" border=\"0\" height=\"31\" width=\"146\" alt=\"Abschicken\" title=\"Abschicken\" /></a>";
			echo "</form>";
			echo "</td>";

			echo "</tr></table></div>\n";
		} elseif ($view_abschicken) {
			$pass_final_recaptcha = true;
			if ($cfg_recaptcha_enabled) {
				if (!isset($_POST['g-recaptcha-response'])) {
					$pass_final_recaptcha = false;
				} else {
					$recaptcha = new \ReCaptcha\ReCaptcha($cfg_recaptcha_privkey);
					$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
					$pass_final_recaptcha = $resp->isSuccess();
				}
			}

			if (!$pass_final_recaptcha) {
				echo '<h1>Bitte Sicherheitsfrage beantworten</h1>';
				echo '<p>Bitte tippen Sie den dargestellten Code ab. Dadurch wird sichergestellt dass Sie ein Mensch und kein Spam-Bot sind.</p>';

				echo "<form action=\"$inphp\" method=\"post\" name=\"frm1\">\n";

				echo '<div class="g-recaptcha" data-sitekey="'.$cfg_recaptcha_pubkey.'"></div>';
				echo '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>';
				echo '<br>';

				echo "<input type=\"hidden\" name=\"action\" value=\"abschicken\" />\n";
				echo "<input type=\"hidden\" name=\"name\" value=\"".myhtmlentities($name)."\" />\n";
				echo "<input type=\"hidden\" name=\"ort\" value=\"".myhtmlentities($ort)."\" />\n";
				echo "<input type=\"hidden\" name=\"email\" value=\"".myhtmlentities($email)."\" />\n";
				echo "<input type=\"hidden\" name=\"homepage\" value=\"".myhtmlentities($homepage)."\" />\n";
				echo "<input type=\"hidden\" name=\"icq\" value=\"".myhtmlentities($icq)."\" />\n";
				echo "<input type=\"hidden\" name=\"nachricht\" value=\"".myhtmlentities($nachricht)."\" />\n";
				if ($cfg_feature_simple_antispam) echo "<input type=\"hidden\" name=\"antispam\" value=\"".myhtmlentities($antispam)."\" />\n";
				echo "<a href=\"javascript:document.frm1.submit()\"><img src=\"images/buttons/abschicken.gif\" border=\"0\" height=\"31\" width=\"146\" alt=\"Abschicken\" title=\"Abschicken\" /></a>";
				echo "</form>";
			} else {
				$daten  = "'".db_real_escape_string($name)."'";
				$felder = '`name`';

				if ($ort != '') {
					$daten  .= ", '".db_real_escape_string($ort)."'";
					$felder .= ', `ort`';
				}

				if ($email != '') {
					$daten  .= ", '".db_real_escape_string($email)."'";
					$felder .= ', `email`';
				}

				if ($homepage != '') {
					$daten  .= ", '".db_real_escape_string($homepage)."'";
					$felder .= ', `homepage`';
				}

				if ($icq != '') {
					$daten  .= ", '".db_real_escape_string($icq)."'";
					$felder .= ', `icq`';
				}

				$daten  .= ", '".db_real_escape_string("$datum $zeit")."'";
				$felder .= ', `timestamp`';

				$daten  .= ", '".db_real_escape_string($ip)."'";
				$felder .= ', `ip`';

				$daten  .= ", '".db_real_escape_string($nachricht)."'";
				$felder .= ', `nachricht`';

				$show = $cfg_automatisch_freischalten ? '1' : '0';
				$daten  .= ", '".db_real_escape_string($show)."'";
				$felder .= ', `show`';

				$result = db_query("INSERT INTO `".db_real_escape_string($table_entries)."` ($felder) VALUES ($daten)");
				$id = db_insert_id();

				$md5 = md5($nachricht);
				$md5_valid = md5_valid($id, $md5);

				// Send mail

				$ger_datum = date('d.m.Y', strtotime($datum));

				$msg_html  = "<h1>".myhtmlentities($seitentitel)." - G&auml;stebucheintrag</h1>\n";
				$msg_html .= "<b>Name:</b> ".myhtmlentities($name)."<br />\n";
				$msg_html .= "<b>E-Mail:</b> ".myhtmlentities($email)."<br />\n";
				$msg_html .= "<b>Ort:</b> ".myhtmlentities($ort)."<br />\n";
				$msg_html .= "<b>Homepage:</b> ".myhtmlentities($homepage)."<br />\n";
				$msg_html .= "<b>ICQ:</b> ".myhtmlentities($icq)."<br />\n";
				$msg_html .= "<b>Datum:</b> $ger_datum<br />\n";
				$msg_html .= "<b>Uhrzeit:</b> $zeit<br />\n";
				$msg_html .= "<br />\n";
				$msg_html .= "<b>Nachricht:</b> ".parse_html($nachricht, $this_dir)."<br />\n";
				$msg_html .= "<br />\n";
				$msg_html .= "<i>Datensatz Nr. $id wurde erstellt.</i><br />\n";
				$msg_html .= "<br />\n";
				$msg_html .= "<font size=\"+1\"><b>Absenderdaten:</b></font><br />\n";
				$msg_html .= "<br />\n";
				$msg_html .= "<b>IP-Adresse:</b> <a href=\"$whois_service\" target=\"_blank\">$ip</a> ($host)<br />\n";
				$msg_html .= "<b>Browser:</b> ".$_SERVER['HTTP_USER_AGENT']."<br />\n";
				$msg_html .= "<br />\n";
				if (!$cfg_automatisch_freischalten) {
					$msg_html .= '<i>Klicken Sie <a href="'."$inphp?action=freischalten&amp;id=$id&amp;md5=".urlencode($md5_valid).'" target="_blank">hier</a>, um den Eintrag freizuschalten. Schalten Sie den Eintrag nicht frei, so bleibt er unver&ouml;ffentlicht.</i><br />'."\n";
				}

				$msg_plain  = "$seitentitel - Gästebucheintrag\n\n";
				$msg_plain .= "Name: $name\n";
				$msg_plain .= "E-Mail: $email\n";
				$msg_plain .= "Ort: $ort\n";
				$msg_plain .= "Homepage: $homepage\n";
				$msg_plain .= "ICQ: $icq\n";
				$msg_plain .= "Datum: $ger_datum\n";
				$msg_plain .= "Uhrzeit: $zeit\n";
				$msg_plain .= "\n";
				$msg_plain .= "Nachricht: $nachricht\n";
				$msg_plain .= "\n";
				$msg_plain .= "Datensatz Nr. $id wurde erstellt.\n";
				$msg_plain .= "\n";
				$msg_plain .= "Absenderdaten:\n";
				$msg_plain .= "\n";
				$msg_plain .= "IP-Adresse: $ip ($host) - Whois-Lookup at $whois_service\n";
				$msg_plain .= "Browser: ".$_SERVER['HTTP_USER_AGENT']."\n";
				$msg_plain .= "\n";
				if (!$cfg_automatisch_freischalten) {
					$msg_plain .= "Öffnen Sie $inphp?action=freischalten&id=$id&md5=".urlencode($md5_valid)." , um den Eintrag freizuschalten. Schalten Sie den Eintrag nicht frei, so bleibt er unver&ouml;ffentlicht.\n";
				}

				$h = new SecureMailer(); // Includes Anti Mail-Header-Injection

/*
				# http://www.phpbox.de/tipsundtricks/emailsumlaute.php
				$h->addHeader('Mime-Version', '1.0');
				$h->addHeader('Content-Type', 'text/html; charset='.$charset);

				$h->addHeader('Content-Transfer-Encoding', 'quoted-printable');
#				$msg_html = wordwrap($msg_html);
#				$msg_html = quoted_printable_encode($msg_html);
				$msg_html = quoted_printable_encode2($msg_html);
*/

				if ($cfg_from_email != '') {
					$h->addHeader('From',     $cfg_from_email);
				}
				if ($email != '') {
					$h->addHeader('Reply-To', $email);
				}

				if ($adminmail_cc != '') {
					$h->addHeader('CC', $adminmail_cc);
				}

				$h->addHeader('X-Mailer', 'PHP/'.phpversion());

				$subject = "$seitentitel - Gästebucheintrag";
				$subject = SecureMailer::utf8Subject($subject);

#				$gesendet = $h->sendMail($adminmail, $subject, $msg_html);
				$gesendet = $h->sendMailHTMLandPlainMultipart($adminmail, $subject, $msg_html, $msg_plain);

				if (!$gesendet) {
					echo '<p><font color="'.$farbe6.'">FEHLER BEIM SENDEN DER MAIL!</font></p>';
				}

				if ($result) {
					echo '<h1 align="center">G&auml;stebucheintrag abgeschickt</h1>

<div align="center">

<p><font color="'.$farbe8.'">Ihr G&auml;stebucheintrag wurde erfolgreich eingetragen!</font></p>

<p>Bitte beachten Sie, dass der Eintrag erst nach einer Pr&uuml;fung des Seiteninhabers freigeschaltet wird.</p>

<p><a href="'.$inphp.'"><img src="images/buttons/zurueck.gif" alt="Zur&uuml;ck" title="Zur&uuml;ck" height="31" width="146" border="0" /></a></p>

</div>';

				} else {
					echo "<p>".db_error()."</p>";
					echo '<p><font color="'.$farbe6.'">Es ist ein schwerer Fehler aufgetreten. Versuchen Sie es nocheinmal.</font></p>';
				}
			}
		}
	}
}

if ($relfehler || $view_eintrag) {
	echo '<h1>G&auml;stebucheintrag</h1>';

	echo '<p>Bitte f&uuml;llen Sie die unteren Felder aus. Die mit <font color="'.$farbe7.'">*</font> gekennzeichneten
	Felder m&uuml;ssen ausgef&uuml;llt werden! Um Missbrauch zu vermeiden, wird die
	IP-Adresse gespeichert.';
	echo ' Die Eintr&auml;ge werden erst nach einer Pr&uuml;fung ver&ouml;ffentlicht.';
	echo '</p>';

	if ($relfehler != '') {
		echo "<p>$relfehler</p>";
	}

	echo '<br />

<form action="'.$inphp.'" method="post" name="frm">
<input type="hidden" name="action" value="'.($cfg_vorschau ? 'vorschau' : 'abschicken').'" />

<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td align="right">';

	if ($err_name) {
		echo '<font color="'.$farbe6.'">Name *:</font>';
	} else {
		echo 'Name <font color="'.$farbe7.'">*</font>:';
	}

	echo '</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><input maxlength="40" size="66" name="name" value="'.$name.'" /></td>
		</tr>
		<tr>
			<td align="right">Wohnort:</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><input maxlength="40" size="66" name="ort" value="'.$ort.'" /></td>
		</tr>
		<tr>
			<td align="right">';

	if ($err_email) {
		echo '<font color="'.$farbe6.'">E-Mail:</font>';
	} else {
		echo 'E-Mail:';
	}

	echo '</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><input maxlength="40" size="66" name="email" value="'.$email.'" /></td>
		</tr>
		<tr>
			<td align="right">';

	if ($err_homepage) {
		echo '<font color="'.$farbe6.'">Homepage:</font>';
	} else {
		echo 'Homepage:';
	}

	echo '</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><input maxlength="40" size="66" name="homepage" value="'.$homepage.'" /></td>
		</tr>
		<tr>
			<td align="right">';

	if ($err_icq) {
		echo '<font color="'.$farbe6.'">ICQ:</font>';
	} else {
		echo 'ICQ:';
	}

	echo '</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><input maxlength="40" size="66" name="icq" value="'.$icq.'" /></td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';

	if ($cfg_feature_simple_antispam) {
		echo '<tr>
			<td align="right">';

		if ($err_antispam) {
			echo '<font color="'.$farbe6.'">Bitte <!--&quot;-->'.$antispam_awaiting.'<!--&quot;--> eingeben *:</font>';
		} else {
			echo 'Bitte <!--&quot;-->'.$antispam_awaiting.'<!--&quot;--> eingeben <font color="'.$farbe7.'">*</font>:';
		}

		echo '</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><input maxlength="40" size="66" name="antispam" value="'.$antispam.'" /> (AntiSpam-Frage)</td>
		</tr>

		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';
	}

	echo '<tr>
			<td align="right">Smileys:</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><script language="JavaScript" type="text/javascript">
		<!--
		function setsmiley(smiley) {
			frm.nachricht.value = frm.nachricht.value + smiley;
		}
		// -->
		</script>';

	$result = db_query("SELECT `zeichen`, `image`, `beschreibung` FROM `".db_real_escape_string($table_smileys)."` WHERE `enabled` = '1' AND `show_in_editor` = '1' ORDER BY `id` ASC");
	while ($row = db_fetch_object($result)) {
		echo "<a href=\"javascript:setsmiley(' ".addslashes(myhtmlentities($row->zeichen))." ')\">".
		'<img src="images/smileys/'.$row->image.'" border="0" alt="'.myhtmlentities($row->beschreibung).'" title="'.myhtmlentities($row->beschreibung).'" /></a>&nbsp;';
	}

	echo '</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td valign="top" align="right">';

	if ($err_nachricht) {
		echo '<font color="'.$farbe6.'">Nachricht *:</font>';
	} else {
		echo 'Nachricht <font color="'.$farbe7.'">*</font>:';
	}

	echo '</td>
			<td><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><textarea name="nachricht" rows="5" cols="50">'.$nachricht.'</textarea><!-- wrap="virtual" --></td>
		</tr>
		<tr>
			<td colspan="2"><img src="images/spacer.gif" height="1" width="10" alt="" /></td>
			<td><br />
			<a href="javascript:document.frm.submit()">';
	if ($cfg_vorschau) {
		echo '<img height="31" alt="Vorschau" title="Vorschau" src="images/buttons/vorschau.gif" width="146" border="0" />';
	} else {
		echo '<img src="images/buttons/abschicken.gif" border="0" height="31" width="146" alt="Abschicken" title="Abschicken" />';
	}
	echo '</a>		<!--
			<img src="images/spacer.gif" height="1" width="30" alt="" />
			<a href="javascript:document.frm.reset()">
			<img height="31" alt="L&ouml;schen" title="L&ouml;schen" src="images/buttons/loeschen.gif" width="146" border="0" /></a>
			-->
			</td>
		</tr>
	</table>

</form>

<p align="center"><a href="'.$inphp.'">Zur&uuml;ck zu den Eintr&auml;gen</a></p>';
} else if ((!$view_vorschau) && (!$view_abschicken) && (!$view_eintrag)) {
	echo '<h1 align="center">G&auml;stebuch</h1>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="center">[ <a href="'.$inphp.'?action=eintrag">Neuen Eintrag hinzuf&uuml;gen</a> ]</td></tr></table><br />';

	$cond = ($cfg_unfreigeschaltete_anzegen) ? '' : " WHERE `show` = '1'";

	$result = db_query("SELECT * FROM `".db_real_escape_string($table_entries)."`$cond");
	if ($result) $number = db_num_rows($result); else $number = 0;
	$max_page = ceil($number / $eintraege_proseite);

	$seiten = isset($_REQUEST['seiten']) ? $_REQUEST['seiten'] : 1;
	if (!isset($seiten) || ($seiten > $max_page) || ($seiten < 0)) $seiten = '1';

	$result = db_query("SELECT * FROM `".db_real_escape_string($table_entries)."`$cond ORDER BY `id` DESC LIMIT ".($seiten-1)*$eintraege_proseite.",".$eintraege_proseite);

	$keineeintraege = true;

	if ($result) {
		while ($row = db_fetch_object($result)) {
			$xry   = explode(' ', $row->timestamp);
			$datum = $xry[0];
			$zeit  = $xry[1];
			anznachricht($row->name, $row->ort, $row->email, $row->homepage, $row->icq, $row->nachricht, $row->kommentar, $zeit, $datum);
			$keineeintraege = false;
		}
	}

	if ($keineeintraege) {
		echo '<div align="center">Es sind keine Eintr&auml;ge vorhanden!</div>';
	}

	if ((!$keineeintraege) && ($max_page != 1)) {
		echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td align="center"><p class="klein2">Seite: ';
		for ($i=1; $i<=$max_page; $i++) {
			if ($seiten != $i) {
				echo "<a href=\"$inphp?seiten=$i\">[$i]</a>\n";
			} else {
				echo "<b>[$i]</b>\n";
			}
		}
		echo '</p></td></tr></table>';
	}

}

echo '<p align="center">OpenSource PHP-G&auml;stebuch von <a href="http://www.viathinksoft.de/" target="_blank">ViaThinkSoft</a>, Version '.$version.'</p>';

echo $seitenfuss;

# ---

// This is the only function which works with GMX.
// wordwrap+quoted_printable_encode does not work, since it would insert whitespaces.
// http://www.php.net/manual/en/function.quoted-printable-encode.php#97230
function quoted_printable_encode2($input, $line_max = 75) {
	$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
	$lines = preg_split("/(?:\r\n|\r|\n)/", $input);
	$linebreak = "=0D=0A=\r\n";
	/* the linebreak also counts as characters in the mime_qp_long_line
	 * rule of spam-assassin */
	$line_max = $line_max - strlen($linebreak);
	$escape = "=";
	$output = "";
	$cur_conv_line = "";
	$length = 0;
	$whitespace_pos = 0;
	$addtl_chars = 0;

	// iterate lines
	for ($j=0; $j<count($lines); $j++) {
		$line = $lines[$j];
		$linlen = strlen($line);

		// iterate chars
		for ($i = 0; $i < $linlen; $i++) {
			$c = substr($line, $i, 1);
			$dec = ord($c);

			$length++;

			if ($dec == 32) {
					// space occurring at end of line, need to encode
					if (($i == ($linlen - 1))) {
						$c = "=20";
						$length += 2;
					}

					$addtl_chars = 0;
					$whitespace_pos = $i;
			} elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) {
					$h2 = floor($dec/16); $h1 = floor($dec%16);
					$c = $escape . $hex[$h2] . $hex[$h1];
					$length += 2;
					$addtl_chars += 2;
			}

			// length for wordwrap exceeded, get a newline into the text
			if ($length >= $line_max) {
				$cur_conv_line .= $c;

				// read only up to the whitespace for the current line
				$whitesp_diff = $i - $whitespace_pos + $addtl_chars;

				/* the text after the whitespace will have to be read
				 * again ( + any additional characters that came into
				 * existence as a result of the encoding process after the whitespace)
				 *
				 * Also, do not start at 0, if there was *no* whitespace in
				 * the whole line */
				if (($i + $addtl_chars) > $whitesp_diff) {
						$output .= substr($cur_conv_line, 0, (strlen($cur_conv_line) - $whitesp_diff)) . $linebreak;
						$i =  $i - $whitesp_diff + $addtl_chars;
					} else {
						$output .= $cur_conv_line . $linebreak;
					}

				$cur_conv_line = "";
				$length = 0;
				$whitespace_pos = 0;
			} else {
				// length for wordwrap not reached, continue reading
				$cur_conv_line .= $c;
			}
		} // end of for

		$length = 0;
		$whitespace_pos = 0;
		$output .= $cur_conv_line;
		$cur_conv_line = "";

		if ($j<=count($lines)-1) {
			$output .= $linebreak;
		}
	} // end for

	return trim($output);
} // end quoted_printable_encode
