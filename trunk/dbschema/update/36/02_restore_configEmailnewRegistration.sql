-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of option configEmailnewRegistration to configEmailnewRegistrations
-- On some installations the option name is without the S, probably due to a bug that went unnoticed.
-- We try to retrieve the value of the old option in the new one if the value still exists.
UPDATE wkx_config AS t1
    INNER JOIN wkx_config AS t2
        ON t1.configName = 'configEmailnewRegistration'
            AND t2.configName = 'configEmailnewRegistrations'
SET t2.configVarchar = t1.configVarchar;

-- Remove configEmailnewRegistration option
DELETE FROM wkx_config
WHERE configName = 'configEmailnewRegistration';
