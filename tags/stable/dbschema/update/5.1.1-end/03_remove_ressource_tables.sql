-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Remove unused ressource tables
-- 

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_quote;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_quote_text;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_text;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_musing;
DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text;
