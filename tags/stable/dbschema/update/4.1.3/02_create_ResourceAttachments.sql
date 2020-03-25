-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create statistics table
-- Rename table attachments => resource_attachments
-- Redefine resource_attachments.filename size
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%statistics` (
	`id` int(11) AUTO_INCREMENT NOT NULL, 
	`resourceId` int(11) NOT NULL,
	`attachmentId` int(11) default NULL,
	`statistics` text, 
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARSET=utf8;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%attachments RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_attachments`;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `filename` VARCHAR(255) NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD `downloads` INT(11) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD `downloadsPeriod` INT(11) DEFAULT 0;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD `primary` VARCHAR(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD `timestamp` DATETIME DEFAULT '00/00/00 00:00:00';
