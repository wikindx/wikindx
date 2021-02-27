-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Redefine categoryCategory index.

CREATE INDEX `categoryCategory` ON %%WIKINDX_DB_TABLEPREFIX%%category (`categoryCategory`(768));
