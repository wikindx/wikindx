-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_custom` (
  `resourcecustomId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcecustomCustomId` int(11) NOT NULL,
  `resourcecustomResourceId` int(11) NOT NULL,
  `resourcecustomShort` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcecustomLong` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcecustomAddUserIdCustom` int(11) DEFAULT NULL,
  `resourcecustomEditUserIdCustom` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcecustomId`),
  KEY `resourcecustomCustomId` (`resourcecustomCustomId`),
  KEY `resourcecustomResourceId` (`resourcecustomResourceId`),
  FULLTEXT KEY `resourcecustomLong` (`resourcecustomLong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
