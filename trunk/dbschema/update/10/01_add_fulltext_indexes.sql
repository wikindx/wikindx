-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add FULLTEXT indexes to speed the quick search

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom ADD FULLTEXT(resourcecustomLong);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata ADD FULLTEXT(resourcemetadataText);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text ADD FULLTEXT(resourcetextAbstract);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text ADD FULLTEXT(resourcetextNote);
