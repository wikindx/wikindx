--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Copy the value of option configBibutilsPath to configBinFolderBibutils
INSERT INTO config (configName, configVarchar)
    SELECT
        'configBinFolderBibutils',
        IFNULL(configVarchar, '')
    FROM config
    WHERE configName = 'configBibutilsPath';

-- Remove configBibutilsPath option
DELETE FROM config
WHERE configName = 'configBibutilsPath';
