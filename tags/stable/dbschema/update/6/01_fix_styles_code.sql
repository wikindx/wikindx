-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Fix the code of styles
-- 
-- https://mathiasbynens.be/notes/mysql-utf8mb4

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = lower(configVarchar)
WHERE configName = 'configStyle';
