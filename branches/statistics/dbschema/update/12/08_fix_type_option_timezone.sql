-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Change the type of the timezone option

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET
	configVarchar = configText,
	configText = NULL
WHERE
	configName = 'configTimezone'
	AND configText IS NOT NULL;
