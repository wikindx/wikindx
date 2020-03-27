-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
--  Create new table resource_text
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_text` (
	`id` int(11) NOT NULL, 
	`note` text, 
	`abstract` text, 
	`urls` text,
	`urlText` text,
	`editUserIdNote` int(11) default NULL,
	`addUserIdNote` int(11) default NULL,
	`editUserIdAbstract` int(11) default NULL,
	`addUserIdAbstract` int(11) default NULL,
	PRIMARY KEY (`id`)
)  ENGINE=MyISAM CHARSET=utf8;
