-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add new table to keep the current internal version number of components and core

-- Needed because we backtracked it
DROP TABLE IF EXISTS`WIKINDX_DB_TABLEPREFIX%%version`;

CREATE TABLE IF NOT EXISTS `wkx_version` (
  `versionComponentType` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `versionComponentId` varchar(256) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `versionInternalVersion` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`versionComponentType`, `versionComponentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
