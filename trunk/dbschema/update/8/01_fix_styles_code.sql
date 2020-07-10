-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Fix the code of styles

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = lower(configVarchar)
WHERE configName IN ('configStyle', 'configRssBibstyle', 'configCmsBibstyle');

UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersStyle = lower(usersStyle);
