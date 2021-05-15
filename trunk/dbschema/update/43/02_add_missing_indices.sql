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
-- Add a missing index on resourceattachmentsResourceId (previous upgrade code missing).
-- Add a missing index on resourceattachmentsTimestamp.
-- Add a missing index on resourcecategorySubcategoryId.
-- Add a missing index on resourcecreatorRole.
-- Add a missing index on resourcecustomCustomId (previous upgrade code missing).
-- Add a missing index on resourcekeywordMetadataId.
-- Add a missing index on resourcelanguageLanguageId (previous upgrade code missing).
-- Add a missing index on resourcelanguageResourceId (previous upgrade code missing).
-- Add a missing index on resourcemetadataPrivate.
-- Add a missing index on resourcemetadataType.
-- Add a missing index on resourcemiscAddUserIdResource.
-- Add a missing index on resourcemiscCollection (previous upgrade code missing).
-- Add a missing index on resourcemiscEditUserIdResource.
-- Add a missing index on resourcemiscPeerReviewed.
-- Add a missing index on resourcemiscPublisher (previous upgrade code missing).
-- Add a missing index on resourcemiscQuarantine.
-- Add a missing index on resourcetextAddUserIdAbstract.
-- Add a missing index on resourcetextAddUserIdNote.
-- Add a missing index on resourcetextEditUserIdAbstract.
-- Add a missing index on resourcetextEditUserIdNote.
-- Add a missing index on resourcetimestampTimestamp (previous upgrade code missing).
-- Add a missing index on resourcetimestampTimestamp (previous upgrade code missing).
-- Add a missing index on resourcetimestampTimestampAdd (previous upgrade code missing).
-- Add a missing index on resourceusertagsTagId.
-- Add a missing index on resourceyearYear2.
-- Add a missing index on resourceyearYear3.
-- Add a missing index on resourceyearYear4.
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

CREATE INDEX `collectionType` ON wkx_collection (`collectionType`);
CREATE INDEX `publisherType` ON wkx_publisher (`publisherType`);
CREATE INDEX `resourceattachmentsEmbargo` ON wkx_resource_attachments (`resourceattachmentsEmbargo`);
CREATE INDEX `resourceattachmentsEmbargoUntil` ON wkx_resource_attachments (`resourceattachmentsEmbargoUntil`);
CREATE INDEX `resourceattachmentsHashFilename` ON wkx_resource_attachments (`resourceattachmentsHashFilename`);
CREATE INDEX `resourceattachmentsPrimary` ON wkx_resource_attachments (`resourceattachmentsPrimary`);
CREATE INDEX `resourceattachmentsResourceId` ON wkx_resource_attachments (`resourceattachmentsResourceId`);
CREATE INDEX `resourceattachmentsTimestamp` ON wkx_resource_attachments (`resourceattachmentsTimestamp`);
CREATE INDEX `resourcecategorySubcategoryId` ON wkx_resource_category (`resourcecategorySubcategoryId`);
CREATE INDEX `resourcecreatorRole` ON wkx_resource_creator (`resourcecreatorRole`);
CREATE INDEX `resourcecustomCustomId` ON wkx_resource_custom (`resourcecustomCustomId`);
CREATE INDEX `resourcekeywordMetadataId` ON wkx_resource_keyword (`resourcekeywordMetadataId`);
CREATE INDEX `resourcelanguageLanguageId` ON wkx_resource_language (`resourcelanguageLanguageId`);
CREATE INDEX `resourcelanguageResourceId` ON wkx_resource_language (`resourcelanguageResourceId`);
CREATE INDEX `resourcemetadataPrivate` ON wkx_resource_metadata (`resourcemetadataPrivate`);
CREATE INDEX `resourcemetadataType` ON wkx_resource_metadata (`resourcemetadataType`);
CREATE INDEX `resourcemiscAddUserIdResource` ON wkx_resource_misc (`resourcemiscAddUserIdResource`);
CREATE INDEX `resourcemiscCollection` ON wkx_resource_misc (`resourcemiscCollection`);
CREATE INDEX `resourcemiscEditUserIdResource` ON wkx_resource_misc (`resourcemiscEditUserIdResource`);
CREATE INDEX `resourcemiscPeerReviewed` ON wkx_resource_misc (`resourcemiscPeerReviewed`);
CREATE INDEX `resourcemiscPublisher` ON wkx_resource_misc (`resourcemiscPublisher`);
CREATE INDEX `resourcemiscQuarantine` ON wkx_resource_misc (`resourcemiscQuarantine`);
CREATE INDEX `resourcetextAddUserIdAbstract` ON wkx_resource_text (`resourcetextAddUserIdAbstract`);
CREATE INDEX `resourcetextAddUserIdNote` ON wkx_resource_text (`resourcetextAddUserIdNote`);
CREATE INDEX `resourcetextEditUserIdAbstract` ON wkx_resource_text (`resourcetextEditUserIdAbstract`);
CREATE INDEX `resourcetextEditUserIdNote` ON wkx_resource_text (`resourcetextEditUserIdNote`);
CREATE INDEX `resourcetimestampTimestamp` ON wkx_resource_timestamp (`resourcetimestampTimestamp`);
CREATE INDEX `resourcetimestampTimestampAdd` ON wkx_resource_timestamp (`resourcetimestampTimestampAdd`);
CREATE INDEX `resourceusertagsResourceId` ON wkx_resource_user_tags (`resourceusertagsResourceId`);
CREATE INDEX `resourceusertagsTagId` ON wkx_resource_user_tags (`resourceusertagsTagId`);
CREATE INDEX `resourceyearYear2` ON wkx_resource_year (`resourceyearYear2`(100));
CREATE INDEX `resourceyearYear3` ON wkx_resource_year (`resourceyearYear3`(100));
CREATE INDEX `resourceyearYear4` ON wkx_resource_year (`resourceyearYear4`(100));
CREATE INDEX `subcategoryCategoryId` ON wkx_subcategory (`subcategoryCategoryId`);
CREATE INDEX `tempstorageTimestamp` ON wkx_temp_storage (`tempstorageTimestamp`);
CREATE INDEX `userbibliographyresourceBibliographyId` ON wkx_user_bibliography_resource (`userbibliographyresourceBibliographyId`);
CREATE INDEX `userbibliographyUserGroupId` ON wkx_user_bibliography (`userbibliographyUserGroupId`);
CREATE INDEX `userbibliographyUserId` ON wkx_user_bibliography (`userbibliographyUserId`);
CREATE INDEX `usergroupsAdminId` ON wkx_user_groups (`usergroupsAdminId`);
CREATE INDEX `usergroupsusersGroupId` ON wkx_user_groups_users (`usergroupsusersGroupId`);
CREATE INDEX `usergroupsusersUserId` ON wkx_user_groups_users (`usergroupsusersUserId`);
CREATE INDEX `userkeywordgroupsUserId` ON wkx_user_keywordgroups (`userkeywordgroupsUserId`);
CREATE INDEX `userkgkeywordsKeywordGroupId` ON wkx_user_kg_keywords (`userkgkeywordsKeywordGroupId`);
CREATE INDEX `userregisterConfirmed` ON wkx_user_register (`userregisterConfirmed`);
CREATE INDEX `userregisterTimestamp` ON wkx_user_register (`userregisterTimestamp`);
CREATE INDEX `usersBlock` ON wkx_users (`usersBlock`);
CREATE INDEX `usertagsUserId` ON wkx_user_tags (`usertagsUserId`);
