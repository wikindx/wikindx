-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Remove base64 encoding from global options

UPDATE wkx_config
SET configText = FROM_BASE64(configText)
WHERE
    configName IN ('configNoSort', 'configSearchFilter', 'configDeactivateResourceTypes')
    AND configText IS NOT NULL;
