-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%statistics_resource_views` (
  `statisticsresourceviewsId` int(11) NOT NULL AUTO_INCREMENT,
  `statisticsresourceviewsResourceId` int(11) NOT NULL,
  `statisticsresourceviewsCount` int(11) DEFAULT 0,
  `statisticsresourceviewsMonth` int(11) DEFAULT 0,
  PRIMARY KEY (`statisticsresourceviewsId`),
  KEY `statisticsresourceviewsResourceId` (`statisticsresourceviewsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
