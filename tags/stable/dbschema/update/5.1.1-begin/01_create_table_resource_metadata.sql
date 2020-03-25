-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Create table resource_metadata
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_metadata` (
	`resourcemetadataId` int(11) NOT NULL AUTO_INCREMENT,
	`resourcemetadataResourceId` int(11) DEFAULT NULL,
	`resourcemetadataMetadataId` int(11) DEFAULT NULL,
	`resourcemetadataAddUserId` int(11) DEFAULT NULL,
	`resourcemetadataPageStart` varchar(255) DEFAULT NULL,
	`resourcemetadataPageEnd` varchar(255) DEFAULT NULL,
	`resourcemetadataParagraph` varchar(255) DEFAULT NULL,
	`resourcemetadataSection` varchar(255) DEFAULT NULL,
	`resourcemetadataChapter` varchar(255) DEFAULT NULL,
	`resourcemetadataType` varchar(255) NOT NULL,
	`resourcemetadataPrivate` varchar(255) NOT NULL DEFAULT 'N',
	`resourcemetadataText` text NOT NULL,
	`resourcemetadataTimestamp` datetime DEFAULT current_timestamp(),
	`resourcemetadataTimestampEdited` datetime DEFAULT NULL,
	PRIMARY KEY (`resourcemetadataId`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
