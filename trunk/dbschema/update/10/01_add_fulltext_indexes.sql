-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add FULLTEXT indexes to speed the quick search

ALTER TABLE wkx_resource_custom ADD FULLTEXT(resourcecustomLong);
ALTER TABLE wkx_resource_metadata ADD FULLTEXT(resourcemetadataText);
ALTER TABLE wkx_resource_text ADD FULLTEXT(resourcetextAbstract);
ALTER TABLE wkx_resource_text ADD FULLTEXT(resourcetextNote);
