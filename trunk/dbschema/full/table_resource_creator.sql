-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_creator` (
  `resourcecreatorId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcecreatorResourceId` int(11) NOT NULL,
  `resourcecreatorCreatorId` int(11) DEFAULT NULL,
  `resourcecreatorOrder` int(11) DEFAULT NULL,
  `resourcecreatorRole` int(11) DEFAULT NULL,
  `resourcecreatorCreatorMain` int(11) DEFAULT NULL,
  `resourcecreatorCreatorSurname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourcecreatorId`),
  KEY `resourcecreatorCreatorSurname` (`resourcecreatorCreatorSurname`(100)),
  KEY `resourcecreatorCreatorId` (`resourcecreatorCreatorId`),
  KEY `resourcecreatorResourceId` (`resourcecreatorResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

