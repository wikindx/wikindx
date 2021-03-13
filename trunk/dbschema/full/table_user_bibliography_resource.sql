-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource` (
  `userbibliographyresourceId` int(11) NOT NULL AUTO_INCREMENT,
  `userbibliographyresourceBibliographyId` int(11) DEFAULT NULL,
  `userbibliographyresourceResourceId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userbibliographyresourceId`),
  KEY `userbibliographyresourceBibliographyId` (`userbibliographyresourceBibliographyId`),
  KEY `userbibliographyresourceResourceId` (`userbibliographyresourceResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
