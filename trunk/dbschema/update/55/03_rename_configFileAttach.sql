--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Rename configFileAttach option to configFileAttachAllow

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configName = 'configFileAttachAllow'
WHERE configName = 'configFileAttach';
