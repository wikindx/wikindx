-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_resource` (
  `resourceId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceSubtitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceShortTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransTitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransSubtitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransShortTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTitleSort` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField4` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField5` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField6` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField7` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField8` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField9` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceNoSort` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransNoSort` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceIsbn` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceBibtexKey` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceDoi` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceId`),
  KEY `resourceType` (`resourceType`),
  KEY `resourceTitle` (`resourceTitle`(768))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
