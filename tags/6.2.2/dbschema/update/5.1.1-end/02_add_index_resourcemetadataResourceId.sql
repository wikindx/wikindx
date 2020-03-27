-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add index resourcemetadataResourceId
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography ADD INDEX `userbibliographyTitle` (`userbibliographyTitle`(768));
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata ADD INDEX `resourcemetadataResourceId` (resourcemetadataResourceId);
