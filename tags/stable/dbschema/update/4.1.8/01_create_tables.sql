-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create temporary table temp_resource_creator for resource_creator migration
-- 

SET NAMES utf8;
SET CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%temp_resource_creator` (
	`resourcecreatorId` int(11) NOT NULL AUTO_INCREMENT,
	`resourcecreatorResourceId` int(11),
	`resourcecreatorCreatorId` int(11) DEFAULT NULL,
	`resourcecreatorOrder` int(11) DEFAULT NULL,
	`resourcecreatorRole` int(11) DEFAULT NULL,
	`resourcecreatorCreatorMain` int(11) DEFAULT NULL,
	`resourcecreatorCreatorSurname` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`resourcecreatorId`),
	INDEX (`resourcecreatorCreatorSurname`), 
	INDEX (`resourcecreatorResourceId`), 
	INDEX (`resourcecreatorCreatorId`)
) ENGINE=MyISAM CHARSET=utf8;
