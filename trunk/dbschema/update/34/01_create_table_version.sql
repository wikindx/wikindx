-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add new table to keep the current internal version number of components and core
-- Transfer the internal version number
-- Delete the old internal version number from database_summary

-- Needed because we backtracked it
DROP TABLE IF EXISTS`WIKINDX_DB_TABLEPREFIX%%version`;

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%version` (
  `versionComponentType` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `versionComponentId` varchar(256) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `versionInternalVersion` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`versionComponentType`, `versionComponentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO %%WIKINDX_DB_TABLEPREFIX%%version (
	versionComponentType,
	versionComponentId,
	versionInternalVersion
)
    SELECT
        'core',
        'core',
        databasesummarySoftwareVersion
    FROM %%WIKINDX_DB_TABLEPREFIX%%database_summary;


ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary
DROP COLUMN databasesummarySoftwareVersion;
