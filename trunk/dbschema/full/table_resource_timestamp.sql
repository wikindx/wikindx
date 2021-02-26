-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_timestamp` (
  `resourcetimestampId` int(11) NOT NULL,
  `resourcetimestampTimestamp` datetime DEFAULT current_timestamp(),
  `resourcetimestampTimestampAdd` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`resourcetimestampId`),
  KEY `resourcetimestampTimestampAdd` (`resourcetimestampTimestampAdd`),
  KEY `resourcetimestampTimestamp` (`resourcetimestampTimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
