-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Rebuild indices
-- 

-- Drop old indices
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection	      	DROP INDEX collectionTitle;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection	 		DROP INDEX collectionTitleShort;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection	       	DROP INDEX collectionType;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator 				DROP INDEX firstname;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator 				DROP INDEX surname;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator 				DROP INDEX initials;

-- Add new indices
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_language    ADD INDEX `resourcelanguageResourceId` (`resourcelanguageResourceId`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_language    ADD INDEX `resourcelanguageLanguageId` (`resourcelanguageLanguageId`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year        ADD INDEX `resourceyearYear1` (`resourceyearYear1`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource             ADD INDEX `resourceType` (`resourceType`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp   ADD INDEX `resourcetimestampTimestampAdd` (`resourcetimestampTimestampAdd`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp   ADD INDEX `resourcetimestampTimestamp` (`resourcetimestampTimestamp`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments ADD INDEX `resourceattachmentsResourceId` (`resourceattachmentsResourceId`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags   ADD INDEX `resourceusertagsResourceId` (`resourceusertagsResourceId`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection           ADD INDEX `collectionTitle` (`collectionTitle`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator              ADD INDEX `creatorSurname` (`creatorSurname`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword              ADD INDEX `keywordKeyword` (`keywordKeyword`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category             ADD INDEX `categoryCategory` (`categoryCategory`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher            ADD INDEX `publisherName` (`publisherName`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc        ADD INDEX `resourcemiscCollection` (`resourcemiscCollection`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc        ADD INDEX `resourcemiscPublisher` (`resourcemiscPublisher`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom      ADD INDEX `resourcecustomCustomId` (`resourcecustomCustomId`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics           ADD INDEX `statisticsResourceId` (`statisticsResourceId`);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics           ADD INDEX `statisticsAttachmentId` (`statisticsAttachmentId`);
