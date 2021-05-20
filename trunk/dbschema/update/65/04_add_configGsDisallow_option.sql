--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of option configGsAllow to configGsDisallow
INSERT INTO config (configName, configBoolean)
    SELECT
        'configGsDisallow',
        CASE
            WHEN configBoolean = 1 THEN 0
            WHEN configBoolean = 0 THEN 1
            ELSE 0
        END
    FROM config
    WHERE configName = 'configGsAllow';

-- Remove configSiteMapAllow option
DELETE FROM config
WHERE configName = 'configGsAllow';
