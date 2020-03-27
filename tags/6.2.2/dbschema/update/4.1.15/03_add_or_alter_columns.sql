-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Add or alter columns
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource ADD COLUMN `resourceTitleSort` VARCHAR(255) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache ADD COLUMN `cacheKeywords` MEDIUMTEXT DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN `usersIsCreator` INT(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN `usersListlink` VARCHAR(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN `usersDepartment` VARCHAR(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN `usersTemplateMenu` INT(11) DEFAULT '0';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD COLUMN `usersInstitution` VARCHAR(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD COLUMN `resourceattachmentsEmbargo` VARCHAR(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD COLUMN `resourceattachmentsEmbargoUntil` DATETIME DEFAULT '00/00/00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator ADD COLUMN `creatorSameAs` INT(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator ADD INDEX `creatorSameAs` (creatorSameAs);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc DROP COLUMN resourcemiscAttachDownloads;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment      MODIFY COLUMN `resourcequotecommentPrivate` VARCHAR(255) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment MODIFY COLUMN `resourceparaphrasecommentPrivate` VARCHAR(255) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text        MODIFY COLUMN `resourcemusingtextPrivate` VARCHAR(255) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users                       MODIFY COLUMN `usersNotify` VARCHAR(1) DEFAULT 'N';
