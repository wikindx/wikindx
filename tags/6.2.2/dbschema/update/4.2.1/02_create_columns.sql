-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add columns
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD COLUMN `resourceattachmentsDescription` TEXT DEFAULT NULL;