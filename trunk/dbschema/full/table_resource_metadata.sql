-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_metadata` (
  `resourcemetadataId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcemetadataResourceId` int(11) DEFAULT NULL,
  `resourcemetadataMetadataId` int(11) DEFAULT NULL,
  `resourcemetadataAddUserId` int(11) DEFAULT NULL,
  `resourcemetadataPageStart` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataPageEnd` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataParagraph` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataSection` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataChapter` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataTimestamp` datetime DEFAULT current_timestamp(),
  `resourcemetadataTimestampEdited` datetime DEFAULT NULL,
  `resourcemetadataType` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `resourcemetadataPrivate` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemetadataText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`resourcemetadataId`),
  KEY `resourcemetadataMetadataId` (`resourcemetadataMetadataId`),
  KEY `resourcemetadataResourceId` (`resourcemetadataResourceId`),
  KEY `resourcemetadataAddUserId` (`resourcemetadataAddUserId`),
  FULLTEXT KEY `resourcemetadataText` (`resourcemetadataText`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

