-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Remove fields resourcekeywordQuoteId, resourcekeywordParaphraseId, and resourcekeywordMusingId
-- 

ALTER TABLE  %%WIKINDX_DB_TABLEPREFIX%%resource_keyword DROP COLUMN resourcekeywordQuoteId;
ALTER TABLE  %%WIKINDX_DB_TABLEPREFIX%%resource_keyword DROP COLUMN resourcekeywordParaphraseId;
ALTER TABLE  %%WIKINDX_DB_TABLEPREFIX%%resource_keyword DROP COLUMN resourcekeywordMusingId;
