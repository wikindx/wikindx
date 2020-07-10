-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- If configMailSmtpEncrypt is not set to an authorized value replace it by the new default value (none).

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = 'none'
WHERE
	configName = 'configMailSmtpEncrypt'
	AND configVarchar NOT IN ('none', 'ssl', 'tls');
