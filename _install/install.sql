-- Achtung, nicht kompatibel mit 1.x Daten

-- DROP TABLE `gaestebuch_entries`;

CREATE TABLE `gaestebuch_entries` (
  `id` bigint(21) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `ort` varchar(80) default NULL,
  `email` varchar(80) default NULL,
  `homepage` varchar(80) default NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` varchar(39),
  `nachricht` text NOT NULL,
  `kommentar` text,
  `show` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ;

-- DROP TABLE `gaestebuch_smileys`;

CREATE TABLE `gaestebuch_smileys` (
  `id` bigint(21) NOT NULL auto_increment,
  `zeichen` varchar(20) binary NOT NULL default '',
  `image` varchar(80) NOT NULL default '',
  `beschreibung` varchar(255) NOT NULL default '',
  `show_in_editor` enum('1','0') NOT NULL default '1',
  `enabled` enum('1','0') NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE ( `zeichen` )
) ;

INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':?:', 'frage.gif', 'Fragezeichen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':!:', 'ausrufez.gif', 'Ausrufezeichen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':alien:', 'alien.gif', 'Alien', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':confused:', 'confused.gif', 'verwirrt', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':cool:', 'cool.gif', 'cool', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':hehe:', 'hehe.gif', 'hehe', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':lol:', 'lol.gif', 'lachen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':pissed:', 'pissed.gif', 'Zähne fletschen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':eek:', 'eek.gif', 'boah', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':cry:', 'cry.gif', 'weinen', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':king:', 'king.gif', 'König', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':mad:', 'mad.gif', 'wütend', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':blah:', 'blah.gif', 'blöd', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':tot:', 'death.gif', 'tot', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':angry:', 'angry.gif', 'böse', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':roll:', 'rolleyes.gif', 'Augen verdrehen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':ass:', 'ass.gif', 'Arsch', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES ('::)', 'rolleyes.gif', 'Augen verdrehen', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':)', 'smile.gif', 'fröhlich', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':(', 'frown.gif', 'traurig', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':D', 'biggrin.gif', 'breites Grinsen', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (';D', 'biggrin.gif', 'breites Grinsen', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (';)', 'wink.gif', 'zwinker', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':p', 'tongue.gif', 'bäää', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-o', 'redface.gif', 'verlegen', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES ('???', 'confused.gif', 'verwirrt', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES ('8)', 'cool.gif', 'cool', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':\'-(', 'cry.gif', 'weinen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':[', 'frown.gif', 'traurig', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-[', 'frown.gif', 'traurig', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-X', '005.gif', 'schweigen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':X', '005.gif', 'schweigen', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-*', '007.gif', 'kuss', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':*', '007.gif', 'kuss', '0');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES ('>:(', 'mad.gif', 'wütend', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-/', '004.gif', 'gnumpf', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-)', 'smile.gif', 'fröhlich', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-(', 'frown.gif', 'traurig', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':-D', 'biggrin.gif', 'breites Grinsen', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (';-)', 'wink.gif', 'zwinker', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':P', 'tongue.gif', 'bäää', '1');
INSERT INTO `gaestebuch_smileys` (`zeichen`, `image`, `beschreibung`, `show_in_editor`) VALUES (':o', 'redface.gif', 'verlegen', '1');
