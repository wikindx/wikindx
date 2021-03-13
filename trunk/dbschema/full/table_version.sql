-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the WIKINDX db schema for MySQL
-- 

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%version` (
  `versionComponentType` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `versionComponentId` varchar(256) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `versionInternalVersion` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`versionComponentType`, `versionComponentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO %%WIKINDX_DB_TABLEPREFIX%%version (
	versionComponentId,
	versionInternalVersion
) VALUES (
    'core',
    'core',
	0
);
