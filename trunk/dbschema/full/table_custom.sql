-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%custom` (
  `customId` int(11) NOT NULL AUTO_INCREMENT,
  `customLabel` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `customSize` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'S',
  PRIMARY KEY (`customId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
