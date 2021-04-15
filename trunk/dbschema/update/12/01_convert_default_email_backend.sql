-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- If configMailBackend is not set to an authorized value replace it by the new default value (sendmail).

UPDATE wkx_config
SET configVarchar = 'sendmail'
WHERE
	configName = 'configMailBackend'
	AND configVarchar NOT IN ('sendmail', 'smtp');
