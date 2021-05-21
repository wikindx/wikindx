--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Copy the value of option configSiteMapAllow to configSiteMapDisallow

UPDATE config
SET configName = 'configRssDisplayEditedResources'
WHERE configName = 'configRssDisplay';
