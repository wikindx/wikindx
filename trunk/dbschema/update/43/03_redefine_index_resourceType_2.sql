-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Redefine resourceType index.

CREATE INDEX `resourceType` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceType`);
