-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on tempstorageTimestamp.

CREATE INDEX `tempstorageTimestamp` ON %%WIKINDX_DB_TABLEPREFIX%%temp_storage (`tempstorageTimestamp`);
