-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_year` (
  `resourceyearId` int(11) NOT NULL,
  `resourceyearYear1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear4` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceyearId`),
  KEY `resourceyearYear1` (`resourceyearYear1`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

