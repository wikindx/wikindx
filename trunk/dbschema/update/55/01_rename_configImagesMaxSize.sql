--
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--

-- Rename configImagesMaxSize option to configImgUploadMaxSize

UPDATE %%WIKINDX_DB_TABLEPREFIX%%config
SET configName = 'configImgUploadMaxSize'
WHERE configName = 'configImagesMaxSize';
