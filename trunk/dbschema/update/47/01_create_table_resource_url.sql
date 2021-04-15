-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `wkx_resource_url` (
  `resourceurlId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceurlResourceId` int(11) NOT NULL,
  `resourceurlUrl` VARCHAR(10000) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `resourceurlName` VARCHAR(768) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `resourceurlPrimary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`resourceurlId`),
  KEY `resourceurlResourceId` (`resourceurlResourceId`),
  KEY `resourceurlPrimary` (`resourceurlPrimary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
