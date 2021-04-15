-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_user_bibliography` (
  `userbibliographyId` int(11) NOT NULL AUTO_INCREMENT,
  `userbibliographyUserId` int(11) DEFAULT NULL,
  `userbibliographyUserGroupId` int(11) DEFAULT NULL,
  `userbibliographyTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `userbibliographyDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`userbibliographyId`),
  KEY `userbibliographyUserId` (`userbibliographyUserId`),
  KEY `userbibliographyUserGroupId` (`userbibliographyUserGroupId`),
  KEY `userbibliographyTitle` (`userbibliographyTitle`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
