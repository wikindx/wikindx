-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- If configMailReturnPath was set by mistake to 0/1 because WIKINDX_MAIL_RETURN_PATH was FALSE/TRUE before a migration,
-- replace it for the empty string

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = ''
WHERE
	configName = 'configMailReturnPath'
	AND configVarchar IN ('0', '1');
