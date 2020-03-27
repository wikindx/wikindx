-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create temporary table temp_resource_category for resource_category migration
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%temp_resource_category` (
	`resourcecategoryId` int(11) NOT NULL AUTO_INCREMENT,
	`resourcecategoryResourceId` int(11) DEFAULT NULL,
	`resourcecategoryCategoryId` int(11) DEFAULT NULL,
	`resourcecategorySubcategoryId` int(11) DEFAULT NULL,
	PRIMARY KEY (`resourcecategoryId`),
	INDEX (`resourcecategoryCategoryId`),
	INDEX (`resourcecategoryResourceId`)
) ENGINE=MyISAM CHARSET=utf8;
