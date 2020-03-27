-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create tables language and resource_language
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%language` (
	`languageId` int(11) NOT NULL AUTO_INCREMENT,
	`languageLanguage` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`languageId`)
) ENGINE=MyISAM CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_language` (
	`resourcelanguageId` int(11) NOT NULL AUTO_INCREMENT,
	`resourcelanguageResourceId` int(11),
	`resourcelanguageLanguageId` int(11),
	PRIMARY KEY (`resourcelanguageId`)
) ENGINE=MyISAM CHARSET=utf8;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection DROP COLUMN collectionString;
