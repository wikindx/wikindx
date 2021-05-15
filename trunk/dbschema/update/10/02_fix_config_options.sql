-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Fix misconfigured config options

DELETE FROM wkx_config
WHERE configName IN ('configMaxWriteChunk', 'configCaptchaPublicKey', 'configCaptchaPrivateKey', 'configRegistrationModerate');

UPDATE wkx_config
SET configName = 'configListLink'
WHERE configName = 'configListlink';

UPDATE wkx_config
SET
	configName = 'configDebugEmail',
	configBoolean = '0'
WHERE configName = 'configSqlEmail';

UPDATE wkx_config
SET configText = 'YTowOnt9'
WHERE
	configName = 'configDeactivateResourceTypes'
	AND configText = '';
