-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_import_raw` (
  `importrawId` int(11) NOT NULL,
  `importrawStringId` int(11) DEFAULT NULL,
  `importrawText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `importrawImportType` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`importrawId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
