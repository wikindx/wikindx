-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create new tables user_tags and resource_user_tags
-- 

SET NAMES latin1;
SET CHARACTER SET latin1;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_tags` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`userId` int(11),
	`tag` varchar(255),
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_user_tags` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`tagId` int(11),
	`resourceId` int(11),
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARSET=utf8;
