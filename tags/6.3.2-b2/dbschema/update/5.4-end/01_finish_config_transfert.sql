-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Finish transfers of config table
-- 

DROP TABLE IF EXISTS %%WIKINDX_DB_TABLEPREFIX%%config;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%configtemp RENAME `%%WIKINDX_DB_TABLEPREFIX%%config`;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config ADD INDEX `configName` (`configName`(768));
