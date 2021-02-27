-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Redefine resourceTitle index.

CREATE INDEX `resourceTitle` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceTitle`(768));
