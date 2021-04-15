-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Rename the old table resource_text to resource_text_48 and mirror data,
-- it's quicker that removing the fields because there are FULLTEXT indices in this table


ALTER TABLE wkx_resource_text
RENAME `wkx_resource_text_48`;
