-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Fix the code of templates

UPDATE wkx_config
SET configVarchar = lower(configVarchar)
WHERE configName IN ('configTemplate');

UPDATE wkx_users
SET usersTemplate = lower(usersTemplate);
