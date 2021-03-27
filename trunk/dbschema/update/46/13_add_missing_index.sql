-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on resourcelanguageLanguageId (previous upgrade code missing).

CREATE INDEX `resourcelanguageLanguageId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_language (`resourcelanguageLanguageId`);