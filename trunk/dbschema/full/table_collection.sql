-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%collection` (
  `collectionId` int(11) NOT NULL AUTO_INCREMENT,
  `collectionTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionTitleShort` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionType` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionDefault` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`collectionId`),
  KEY `collectionType` (`collectionType`),
  KEY `collectionTitle` (`collectionTitle`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
