-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Fix misconfigured config options

DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName IN ('configMaxWriteChunk', 'configCaptchaPublicKey', 'configCaptchaPrivateKey', 'configRegistrationModerate');

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configName = 'configListLink'
WHERE configName = 'configListlink';

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET
	configName = 'configDebugEmail',
	configBoolean = '0'
WHERE configName = 'configSqlEmail';

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configText = 'YTowOnt9'
WHERE
	configName = 'configDeactivateResourceTypes'
	AND configText = '';
