-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Rename option configMailServer

DELETE FROM %%WIKINDX_DB_TABLEPREFIX%%config
WHERE configName = 'configMailUse';

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configName = 'configMailUse'
WHERE configName = 'configMailServer';
