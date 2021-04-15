-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Finish transfers of config table
-- 

DROP TABLE IF EXISTS wkx_config;

ALTER TABLE wkx_configtemp RENAME `wkx_config`;

ALTER TABLE wkx_config ADD INDEX `configName` (`configName`(768));
