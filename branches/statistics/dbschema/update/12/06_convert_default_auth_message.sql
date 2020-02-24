-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- If configAuthGateMessage was set by mistake to 0/1 because WIKINDX_MAIL_RETURN_PATH was FALSE/TRUE before a migration,
-- replace it for the empty string

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = ''
WHERE
	configName = 'configAuthGateMessage'
	AND configVarchar IN ('0', '1');
