-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create temporary table temp_resource_keyword for resource_keyword migration
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%temp_resource_keyword` (
	`resourcekeywordId` int(11) NOT NULL AUTO_INCREMENT,
	`resourcekeywordResourceId` int(11) DEFAULT NULL,
	`resourcekeywordQuoteId` int(11) DEFAULT NULL,
	`resourcekeywordParaphraseId` int(11) DEFAULT NULL,
	`resourcekeywordMusingId` int(11) DEFAULT NULL,
	`resourcekeywordKeywordId` int(11) DEFAULT NULL,
	PRIMARY KEY (`resourcekeywordId`),
	INDEX (`resourcekeywordKeywordId`),
	INDEX (`resourcekeywordResourceId`)
) ENGINE=MyISAM CHARSET=utf8;
