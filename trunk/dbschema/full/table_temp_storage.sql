-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%temp_storage` (
  `tempstorageId` char(36) NOT NULL,
  `tempstorageData` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `tempstorageTimestamp` datetime DEFAULT current_timestamp() NOT NULL,
  PRIMARY KEY (`tempstorageId`),
  KEY (`tempstorageTimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
