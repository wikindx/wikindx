-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%config` (
  `configId` int(11) NOT NULL AUTO_INCREMENT,
  `configName` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `configInt` int(11) DEFAULT NULL,
  `configVarchar` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `configText` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `configBoolean` tinyint(1) DEFAULT NULL,
  `configDatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`configId`),
  KEY `configName` (`configName`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

