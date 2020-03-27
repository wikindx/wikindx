-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Drop tables migrated at previous stages
-- 

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_category;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%temp_resource_category RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_category`;

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_keyword;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%temp_resource_keyword RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_keyword`;

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%resource_creator;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%temp_resource_creator RENAME `%%WIKINDX_DB_TABLEPREFIX%%resource_creator`;
