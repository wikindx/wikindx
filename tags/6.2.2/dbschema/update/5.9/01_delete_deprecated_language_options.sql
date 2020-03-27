-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Remove deprecated language options for CMS and RSS
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName IN ('configCmsLanguage', 'configRssLanguage');
