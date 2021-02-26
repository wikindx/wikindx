-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_user_tags` (
  `resourceusertagsId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceusertagsTagId` int(11) DEFAULT NULL,
  `resourceusertagsResourceId` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourceusertagsId`),
  KEY `resourceusertagsTagId` (`resourceusertagsTagId`),
  KEY `resourceusertagsResourceId` (`resourceusertagsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
