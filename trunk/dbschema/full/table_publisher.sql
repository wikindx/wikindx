-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%publisher` (
  `publisherId` int(11) NOT NULL AUTO_INCREMENT,
  `publisherName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `publisherLocation` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `publisherType` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`publisherId`),
  KEY `publisherName` (`publisherName`(100)),
  KEY `publisherType` (`publisherType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
