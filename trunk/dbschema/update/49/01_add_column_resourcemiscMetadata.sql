-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add column resourcemiscMetadata to resource_misc

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc ADD COLUMN resourcemiscMetadata tinyint(1) NOT NULL DEFAULT 0;
