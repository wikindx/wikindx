-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- If configMailReplyTo is not set to an authorized value replace it by the new default value ("").

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = ''
WHERE
	configName = 'configMailReplyTo'
	AND configVarchar = 'noreply@noreply.org';
