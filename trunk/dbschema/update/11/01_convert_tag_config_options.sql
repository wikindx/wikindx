-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Convert tags sizes options to tags scale factors options

-- Convert from float to int
UPDATE wkx_config
SET
	configName = 'configTagLowFactor',
	configInt = COALESCE(configFloat, 1) * 100,
	configFloat = NULL
WHERE configName = 'configTagLowSize';

UPDATE wkx_config
SET
	configName = 'configTagHighFactor',
	configInt = COALESCE(configFloat, 2) * 100,
	configFloat = NULL
WHERE configName = 'configTagHighSize';


-- Fix min values
UPDATE wkx_config
SET configInt = 50
WHERE
	configName = 'configTagLowFactor'
	AND configInt < 50;

UPDATE wkx_config
SET configInt = 50
WHERE
	configName = 'configTagHighFactor'
	AND configInt < 50;


-- Fix max values
UPDATE wkx_config
SET configInt = 200
WHERE
	configName = 'configTagLowFactor'
	AND configInt > 200;

UPDATE wkx_config
SET configInt = 200
WHERE
	configName = 'configTagHighFactor'
	AND configInt > 200;


-- Fix the scalling step to 5
UPDATE wkx_config
SET configInt = configInt - MOD(configInt, 5)
WHERE configName IN('configTagLowFactor', 'configTagHighFactor');
