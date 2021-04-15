-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- If configMailFrom is not set to an authorized value replace it by the new default value ("").

UPDATE wkx_config
SET configVarchar = ''
WHERE
	configName = 'configMailFrom'
	AND configVarchar = 'WIKINDX';
