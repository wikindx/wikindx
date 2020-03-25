-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create new table subcategory
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%subcategory` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`categoryId` int(11),
	`subcategory` varchar(255),
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARSET=utf8;
