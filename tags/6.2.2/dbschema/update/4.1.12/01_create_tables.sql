-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create tables user_bibliography_resource and user_groups_users
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource` (
	`userbibliographyresourceId` int(11) NOT NULL AUTO_INCREMENT,
	`userbibliographyresourceBibliographyId` int(11) DEFAULT NULL,
	`userbibliographyresourceResourceId` int(11) DEFAULT NULL,
	PRIMARY KEY (`userbibliographyresourceId`),
	INDEX (`userbibliographyresourceResourceId`)
) ENGINE=MyISAM CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_groups_users` (
	`usergroupsusersId` int(11) NOT NULL AUTO_INCREMENT,
	`usergroupsusersGroupId` int(11) DEFAULT NULL,
	`usergroupsusersUserId` int(11) DEFAULT NULL,
	PRIMARY KEY (`usergroupsusersId`)
) ENGINE=MyISAM CHARSET=utf8;
