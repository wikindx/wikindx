-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Convert the backend to the new default when it is "mail", which is retired

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = 'sendmail'
WHERE
	configName = 'configMailBackend'
	AND configVarchar = 'mail';
