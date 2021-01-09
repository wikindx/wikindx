-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Delete the old internal version number from database_summary

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary
DROP COLUMN databasesummarySoftwareVersion;
