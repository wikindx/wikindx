-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_text` (
  `resourcetextId` int(11) NOT NULL,
  `resourcetextAddUserIdNote` int(11) DEFAULT NULL,
  `resourcetextEditUserIdNote` int(11) DEFAULT NULL,
  `resourcetextAddUserIdAbstract` int(11) DEFAULT NULL,
  `resourcetextEditUserIdAbstract` int(11) DEFAULT NULL,
  `resourcetextNote` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcetextAbstract` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcetextUrls` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcetextUrlText` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourcetextId`),
  FULLTEXT KEY `resourcetextAbstract` (`resourcetextAbstract`),
  FULLTEXT KEY `resourcetextNote` (`resourcetextNote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
