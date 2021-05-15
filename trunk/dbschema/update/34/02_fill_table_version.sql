-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Transfer the internal version number in version table

INSERT INTO wkx_version (
	versionComponentType,
	versionComponentId,
	versionInternalVersion
)
    SELECT
        'core',
        'core',
        databasesummarySoftwareVersion
    FROM wkx_database_summary;
