-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- If configMailReplyTo is not set to an authorized value replace it by the new default value ("").

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configVarchar = ''
WHERE
	configName = 'configMailReplyTo'
	AND configVarchar = 'noreply@noreply.org';
