-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_tags` (
  `usertagsId` int(11) NOT NULL AUTO_INCREMENT,
  `usertagsUserId` int(11) DEFAULT NULL,
  `usertagsTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`usertagsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

