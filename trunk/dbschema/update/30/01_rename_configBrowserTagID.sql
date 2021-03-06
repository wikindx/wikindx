--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of configBrowserTagID to configBrowserTabID option
UPDATE wkx_config
SET
    configVarchar = IFNULL((
        SELECT configVarchar
        FROM wkx_config
        WHERE configName = 'configBrowserTagID'
    ), configVarchar)
WHERE configName = 'configBrowserTabID';

-- Remove configBrowserTagID option
DELETE FROM wkx_config
WHERE configName = 'configBrowserTagID';
