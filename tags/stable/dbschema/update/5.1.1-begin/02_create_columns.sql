-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add columns
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_keyword ADD COLUMN `resourcekeywordMetadataId` INT(11) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword          ADD COLUMN `keywordGlossary` TEXT DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection       ADD COLUMN `collectionDefault` LONGTEXT DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users            ADD COLUMN `usersNotifyDigestThreshold` INT(11) DEFAULT 100;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom  ADD INDEX  `resourcecustomResourceId` (resourcecustomResourceId);