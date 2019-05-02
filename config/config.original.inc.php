<?php

error_reporting(E_ALL | E_NOTICE | E_DEPRECATED | E_STRICT);

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
