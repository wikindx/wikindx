-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_misc` (
  `resourcemiscId` int(11) NOT NULL,
  `resourcemiscCollection` int(11) DEFAULT NULL,
  `resourcemiscPublisher` int(11) DEFAULT NULL,
  `resourcemiscField1` int(11) DEFAULT NULL,
  `resourcemiscField2` int(11) DEFAULT NULL,
  `resourcemiscField3` int(11) DEFAULT NULL,
  `resourcemiscField4` int(11) DEFAULT NULL,
  `resourcemiscField5` int(11) DEFAULT NULL,
  `resourcemiscField6` int(11) DEFAULT NULL,
  `resourcemiscTag` int(11) DEFAULT NULL,
  `resourcemiscAddUserIdResource` int(11) DEFAULT NULL,
  `resourcemiscEditUserIdResource` int(11) DEFAULT NULL,
  `resourcemiscMaturityIndex` double DEFAULT 0,
  `resourcemiscPeerReviewed` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemiscQuarantine` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemiscMetadata` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`resourcemiscId`),
  KEY `resourcemiscCollection` (`resourcemiscCollection`),
  KEY `resourcemiscPublisher` (`resourcemiscPublisher`),
  KEY `resourcemiscPeerReviewed` (`resourcemiscPeerReviewed`),
  KEY `resourcemiscQuarantine` (`resourcemiscQuarantine`),
  KEY `resourcemiscAddUserIdResource` (`resourcemiscAddUserIdResource`),
  KEY `resourcemiscEditUserIdResource` (`resourcemiscEditUserIdResource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
