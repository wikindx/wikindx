-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `user_groups` (
  `usergroupsId` int(11) NOT NULL AUTO_INCREMENT,
  `usergroupsTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usergroupsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usergroupsAdminId` int(11) NOT NULL,
  PRIMARY KEY (`usergroupsId`),
  KEY (`usergroupsAdminId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
