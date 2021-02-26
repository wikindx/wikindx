-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on collection.
-- Add a missing index on publisherType.
-- Add a missing index on resourceattachmentsEmbargo.
-- Add a missing index on resourceattachmentsEmbargoUntil.
-- Add a missing index on resourceattachmentsHashFilename.
-- Add a missing index on resourceattachmentsPrimary.
-- Add a missing index on resourceattachmentsTimestamp.
-- Add a missing index on resourcecategorySubcategoryId.
-- Add a missing index on resourcecreatorRole.
-- Add a missing index on resourcekeywordMetadataId.
-- Add a missing index on resourcemetadataPrivate.
-- Add a missing index on resourcemetadataType.
-- Add a missing index on resourcemiscAddUserIdResource.
-- Add a missing index on resourcemiscEditUserIdResource.
-- Add a missing index on resourcemiscPeerReviewed.
-- Add a missing index on resourcemiscQuarantine.
-- Add a missing index on resourcetextAddUserIdAbstract.
-- Add a missing index on resourcetextAddUserIdNote.
-- Add a missing index on resourcetextEditUserIdAbstract.
-- Add a missing index on resourcetextEditUserIdNote.
-- Add a missing index on resourceusertagsTagId.
-- Add a missing index on subcategoryCategoryId.
-- Add a missing index on tempstorageTimestamp.
-- Add a missing index on userbibliographyresourceBibliographyId.
-- Add a missing index on userbibliographyUserGroupId.
-- Add a missing index on userbibliographyUserId.
-- Add a missing index on usergroupsAdminId.
-- Add a missing index on usergroupsusersGroupId.
-- Add a missing index on usergroupsusersUserId.
-- Add a missing index on userkeywordgroupsUserId.
-- Add a missing index on userkgkeywordsKeywordGroupId.
-- Add a missing index on userregisterConfirmed.
-- Add a missing index on userregisterTimestamp.
-- Add a missing index on usersBlock.
-- Add a missing index on usertagsUserId.

CREATE INDEX `collectionType` ON %%WIKINDX_DB_TABLEPREFIX%%collection (`collectionType`);
CREATE INDEX `publisherType` ON %%WIKINDX_DB_TABLEPREFIX%%publisher (`publisherType`);
CREATE INDEX `resourceattachmentsEmbargo` ON %%WIKINDX_DB_TABLEPREFIX%%resource_attachments (`resourceattachmentsEmbargo`);
CREATE INDEX `resourceattachmentsEmbargoUntil` ON %%WIKINDX_DB_TABLEPREFIX%%resource_attachments (`resourceattachmentsEmbargoUntil`);
CREATE INDEX `resourceattachmentsHashFilename` ON %%WIKINDX_DB_TABLEPREFIX%%resource_attachments (`resourceattachmentsHashFilename`);
CREATE INDEX `resourceattachmentsPrimary` ON %%WIKINDX_DB_TABLEPREFIX%%resource_attachments (`resourceattachmentsPrimary`);
CREATE INDEX `resourceattachmentsTimestamp` ON %%WIKINDX_DB_TABLEPREFIX%%resource_attachments (`resourceattachmentsTimestamp`);
CREATE INDEX `resourcecategorySubcategoryId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_category (`resourcecategorySubcategoryId`);
CREATE INDEX `resourcecreatorRole` ON %%WIKINDX_DB_TABLEPREFIX%%resource_creator (`resourcecreatorRole`);
CREATE INDEX `resourcekeywordMetadataId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_keyword (`resourcekeywordMetadataId`);
CREATE INDEX `resourcemetadataPrivate` ON %%WIKINDX_DB_TABLEPREFIX%%resource_metadata (`resourcemetadataPrivate`);
CREATE INDEX `resourcemetadataType` ON %%WIKINDX_DB_TABLEPREFIX%%resource_metadata (`resourcemetadataType`);
CREATE INDEX `resourcemiscAddUserIdResource` ON %%WIKINDX_DB_TABLEPREFIX%%resource_misc (`resourcemiscAddUserIdResource`);
CREATE INDEX `resourcemiscEditUserIdResource` ON %%WIKINDX_DB_TABLEPREFIX%%resource_misc (`resourcemiscEditUserIdResource`);
CREATE INDEX `resourcemiscPeerReviewed` ON %%WIKINDX_DB_TABLEPREFIX%%resource_misc (`resourcemiscPeerReviewed`);
CREATE INDEX `resourcemiscQuarantine` ON %%WIKINDX_DB_TABLEPREFIX%%resource_misc (`resourcemiscQuarantine`);
CREATE INDEX `resourcetextAddUserIdAbstract` ON %%WIKINDX_DB_TABLEPREFIX%%resource_text (`resourcetextAddUserIdAbstract`);
CREATE INDEX `resourcetextAddUserIdNote` ON %%WIKINDX_DB_TABLEPREFIX%%resource_text (`resourcetextAddUserIdNote`);
CREATE INDEX `resourcetextEditUserIdAbstract` ON %%WIKINDX_DB_TABLEPREFIX%%resource_text (`resourcetextEditUserIdAbstract`);
CREATE INDEX `resourcetextEditUserIdNote` ON %%WIKINDX_DB_TABLEPREFIX%%resource_text (`resourcetextEditUserIdNote`);
CREATE INDEX `resourceusertagsTagId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags (`resourceusertagsTagId`);
CREATE INDEX `subcategoryCategoryId` ON %%WIKINDX_DB_TABLEPREFIX%%subcategory (`subcategoryCategoryId`);
CREATE INDEX `tempstorageTimestamp` ON %%WIKINDX_DB_TABLEPREFIX%%temp_storage (`tempstorageTimestamp`);
CREATE INDEX `userbibliographyresourceBibliographyId` ON %%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource (`userbibliographyresourceBibliographyId`);
CREATE INDEX `userbibliographyUserGroupId` ON %%WIKINDX_DB_TABLEPREFIX%%user_bibliography (`userbibliographyUserGroupId`);
CREATE INDEX `userbibliographyUserId` ON %%WIKINDX_DB_TABLEPREFIX%%user_bibliography (`userbibliographyUserId`);
CREATE INDEX `usergroupsAdminId` ON %%WIKINDX_DB_TABLEPREFIX%%user_groups (`usergroupsAdminId`);
CREATE INDEX `usergroupsusersGroupId` ON %%WIKINDX_DB_TABLEPREFIX%%user_groups_users (`usergroupsusersGroupId`);
CREATE INDEX `usergroupsusersUserId` ON %%WIKINDX_DB_TABLEPREFIX%%user_groups_users (`usergroupsusersUserId`);
CREATE INDEX `userkeywordgroupsUserId` ON %%WIKINDX_DB_TABLEPREFIX%%user_keywordgroups (`userkeywordgroupsUserId`);
CREATE INDEX `userkgkeywordsKeywordGroupId` ON %%WIKINDX_DB_TABLEPREFIX%%user_kg_keywords (`userkgkeywordsKeywordGroupId`);
CREATE INDEX `userregisterConfirmed` ON %%WIKINDX_DB_TABLEPREFIX%%user_register (`userregisterConfirmed`);
CREATE INDEX `userregisterTimestamp` ON %%WIKINDX_DB_TABLEPREFIX%%user_register (`userregisterTimestamp`);
CREATE INDEX `usersBlock` ON %%WIKINDX_DB_TABLEPREFIX%%users (`usersBlock`);
CREATE INDEX `usertagsUserId` ON %%WIKINDX_DB_TABLEPREFIX%%user_tags (`usertagsUserId`);
