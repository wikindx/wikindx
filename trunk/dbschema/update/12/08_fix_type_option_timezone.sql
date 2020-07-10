-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Change the type of the timezone option

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET
	configVarchar = configText,
	configText = NULL
WHERE
	configName = 'configTimezone'
	AND configText IS NOT NULL;
