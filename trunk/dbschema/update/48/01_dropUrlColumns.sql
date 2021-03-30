-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Drop URL-related columns from resource_text

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text DROP COLUMN resourcetextUrls;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text DROP COLUMN resourcetextUrlText;