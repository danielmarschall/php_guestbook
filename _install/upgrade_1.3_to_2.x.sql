-- Changes from 1.3 to 2.x

-- TODO: auch als php script

-- TODO: tesen

ALTER TABLE `page_gaestebuch` RENAME TO `gaestebuch_entries`;
ALTER TABLE `gaestebuch_entries` MODIFY COLUMN `ip` varchar(39);
ALTER TABLE `gaestebuch_entries` DROP COLUMN `md5`;
ALTER TABLE `gaestebuch_entries` ADD `timestamp` DATETIME NOT NULL AFTER `zeit`;
UPDATE `gaestebuch_entries` SET `timestamp` = CONCAT(`datum`, ' ', `zeit`);
ALTER TABLE `gaestebuch_entries` DROP `datum`, DROP `zeit`;

ALTER TABLE `page_gaestebuch_smilies` RENAME TO `gaestebuch_smileys`;
ALTER TABLE `gaestebuch_smileys` ADD UNIQUE ( `zeichen` );
ALTER TABLE `gaestebuch_smileys` CHANGE COLUMN `viewable` `enabled` enum('0','1') NOT NULL default '0';
ALTER TABLE `gaestebuch_smileys` ADD COLUMN `show_in_editor` enum('1','0') NOT NULL default '1' AFTER `beschreibung`;
