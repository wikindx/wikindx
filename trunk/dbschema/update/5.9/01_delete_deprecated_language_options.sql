-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Remove deprecated language options for CMS and RSS
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName IN ('configCmsLanguage', 'configRssLanguage');
