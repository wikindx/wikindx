-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_keyword` (
  `resourcekeywordId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcekeywordResourceId` int(11) DEFAULT NULL,
  `resourcekeywordMetadataId` int(11) DEFAULT NULL,
  `resourcekeywordKeywordId` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcekeywordId`),
  KEY `resourcekeywordKeywordId` (`resourcekeywordKeywordId`),
  KEY `resourcekeywordMetadataId` (`resourcekeywordMetadataId`),
  KEY `resourcekeywordResourceId` (`resourcekeywordResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
