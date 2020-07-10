-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Remove the float type of the config table

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config
DROP COLUMN configFloat;
