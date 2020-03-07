-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

SET NAMES utf8mb4 COLLATE 'utf8mb4_unicode_520_ci';
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%bibtex_string` (
  `bibtexstringId` int(11) NOT NULL AUTO_INCREMENT,
  `bibtexstringText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`bibtexstringId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%cache` (
  `cacheResourceCreators` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheMetadataCreators` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheKeywords` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheResourceKeywords` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheMetadataKeywords` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheQuoteKeywords` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheParaphraseKeywords` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheMusingKeywords` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheResourcePublishers` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheMetadataPublishers` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheConferenceOrganisers` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheResourceCollections` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheMetadataCollections` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheResourceCollectionTitles` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `cacheResourceCollectionShorts` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%category` (
  `categoryId` int(11) NOT NULL AUTO_INCREMENT,
  `categoryCategory` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`categoryId`),
  KEY `categoryCategory` (`categoryCategory`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%collection` (
  `collectionId` int(11) NOT NULL AUTO_INCREMENT,
  `collectionTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionTitleShort` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `collectionDefault` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`collectionId`),
  KEY `collectionTitle` (`collectionTitle`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%config` (
  `configId` int(11) NOT NULL AUTO_INCREMENT,
  `configName` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `configInt` int(11) DEFAULT NULL,
  `configVarchar` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `configText` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `configBoolean` tinyint(1) DEFAULT NULL,
  `configDatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`configId`),
  KEY `configName` (`configName`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%creator` (
  `creatorId` int(11) NOT NULL AUTO_INCREMENT,
  `creatorSurname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorFirstname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorInitials` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorPrefix` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creatorSameAs` int(11) DEFAULT NULL,
  PRIMARY KEY (`creatorId`),
  KEY `creatorSurname` (`creatorSurname`(100)),
  KEY `creatorSameAs` (`creatorSameAs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%custom` (
  `customId` int(11) NOT NULL AUTO_INCREMENT,
  `customLabel` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `customSize` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'S',
  PRIMARY KEY (`customId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%database_summary` (
  `databasesummaryTotalResources` int(11) NOT NULL,
  `databasesummaryTotalQuotes` int(11) DEFAULT NULL,
  `databasesummaryTotalParaphrases` int(11) DEFAULT NULL,
  `databasesummaryTotalMusings` int(11) DEFAULT NULL,
  `databasesummarySoftwareVersion` varchar(16) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%import_raw` (
  `importrawId` int(11) NOT NULL,
  `importrawStringId` int(11) DEFAULT NULL,
  `importrawText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `importrawImportType` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`importrawId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%keyword` (
  `keywordId` int(11) NOT NULL AUTO_INCREMENT,
  `keywordKeyword` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `keywordGlossary` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`keywordId`),
  KEY `keywordKeyword` (`keywordKeyword`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%language` (
  `languageId` int(11) NOT NULL AUTO_INCREMENT,
  `languageLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`languageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%news` (
  `newsId` int(11) NOT NULL AUTO_INCREMENT,
  `newsTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `newsNews` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `newsTimestamp` datetime DEFAULT current_timestamp(),
  `newsEmailSent` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  PRIMARY KEY (`newsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%publisher` (
  `publisherId` int(11) NOT NULL AUTO_INCREMENT,
  `publisherName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `publisherLocation` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `publisherType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`publisherId`),
  KEY `publisherName` (`publisherName`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource` (
  `resourceId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceSubtitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceShortTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransTitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransSubtitle` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransShortTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTitleSort` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField4` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField5` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField6` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField7` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField8` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceField9` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceNoSort` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceTransNoSort` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceIsbn` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceBibtexKey` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceDoi` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceId`),
  KEY `resourceType` (`resourceType`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_attachments` (
  `resourceattachmentsId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceattachmentsResourceId` int(11) DEFAULT NULL,
  `resourceattachmentsHashFilename` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileName` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileType` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsFileSize` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceattachmentsPrimary` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsTimestamp` datetime DEFAULT current_timestamp(),
  `resourceattachmentsEmbargo` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourceattachmentsEmbargoUntil` datetime DEFAULT current_timestamp(),
  `resourceattachmentsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceattachmentsId`),
  KEY `resourceattachmentsResourceId` (`resourceattachmentsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_category` (
  `resourcecategoryId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcecategoryResourceId` int(11) DEFAULT NULL,
  `resourcecategoryCategoryId` int(11) DEFAULT NULL,
  `resourcecategorySubcategoryId` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcecategoryId`),
  KEY `resourcecategoryCategoryId` (`resourcecategoryCategoryId`),
  KEY `resourcecategoryResourceId` (`resourcecategoryResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_creator` (
  `resourcecreatorId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcecreatorResourceId` int(11) NOT NULL,
  `resourcecreatorCreatorId` int(11) DEFAULT NULL,
  `resourcecreatorOrder` int(11) DEFAULT NULL,
  `resourcecreatorRole` int(11) DEFAULT NULL,
  `resourcecreatorCreatorMain` int(11) DEFAULT NULL,
  `resourcecreatorCreatorSurname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourcecreatorId`),
  KEY `resourcecreatorCreatorSurname` (`resourcecreatorCreatorSurname`(100)),
  KEY `resourcecreatorCreatorId` (`resourcecreatorCreatorId`),
  KEY `resourcecreatorResourceId` (`resourcecreatorResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_custom` (
  `resourcecustomId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcecustomCustomId` int(11) NOT NULL,
  `resourcecustomResourceId` int(11) NOT NULL,
  `resourcecustomShort` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcecustomLong` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcecustomAddUserIdCustom` int(11) DEFAULT NULL,
  `resourcecustomEditUserIdCustom` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcecustomId`),
  KEY `resourcecustomCustomId` (`resourcecustomCustomId`),
  KEY `resourcecustomResourceId` (`resourcecustomResourceId`),
  FULLTEXT KEY `resourcecustomLong` (`resourcecustomLong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_keyword` (
  `resourcekeywordId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcekeywordResourceId` int(11) DEFAULT NULL,
  `resourcekeywordMetadataId` int(11) DEFAULT NULL,
  `resourcekeywordKeywordId` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcekeywordId`),
  KEY `resourcekeywordKeywordId` (`resourcekeywordKeywordId`),
  KEY `resourcekeywordResourceId` (`resourcekeywordResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_language` (
  `resourcelanguageId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcelanguageResourceId` int(11) DEFAULT NULL,
  `resourcelanguageLanguageId` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcelanguageId`),
  KEY `resourcelanguageResourceId` (`resourcelanguageResourceId`),
  KEY `resourcelanguageLanguageId` (`resourcelanguageLanguageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_metadata` (
  `resourcemetadataId` int(11) NOT NULL AUTO_INCREMENT,
  `resourcemetadataResourceId` int(11) DEFAULT NULL,
  `resourcemetadataMetadataId` int(11) DEFAULT NULL,
  `resourcemetadataAddUserId` int(11) DEFAULT NULL,
  `resourcemetadataPageStart` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataPageEnd` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataParagraph` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataSection` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataChapter` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcemetadataTimestamp` datetime DEFAULT current_timestamp(),
  `resourcemetadataTimestampEdited` datetime DEFAULT NULL,
  `resourcemetadataType` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `resourcemetadataPrivate` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemetadataText` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`resourcemetadataId`),
  KEY `resourcemetadataMetadataId` (`resourcemetadataMetadataId`),
  KEY `resourcemetadataResourceId` (`resourcemetadataResourceId`),
  KEY `resourcemetadataAddUserId` (`resourcemetadataAddUserId`),
  FULLTEXT KEY `resourcemetadataText` (`resourcemetadataText`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_misc` (
  `resourcemiscId` int(11) NOT NULL,
  `resourcemiscCollection` int(11) DEFAULT NULL,
  `resourcemiscPublisher` int(11) DEFAULT NULL,
  `resourcemiscField1` int(11) DEFAULT NULL,
  `resourcemiscField2` int(11) DEFAULT NULL,
  `resourcemiscField3` int(11) DEFAULT NULL,
  `resourcemiscField4` int(11) DEFAULT NULL,
  `resourcemiscField5` int(11) DEFAULT NULL,
  `resourcemiscField6` int(11) DEFAULT NULL,
  `resourcemiscTag` int(11) DEFAULT NULL,
  `resourcemiscAddUserIdResource` int(11) DEFAULT NULL,
  `resourcemiscEditUserIdResource` int(11) DEFAULT NULL,
  `resourcemiscMaturityIndex` double DEFAULT 0,
  `resourcemiscPeerReviewed` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `resourcemiscQuarantine` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  PRIMARY KEY (`resourcemiscId`),
  KEY `resourcemiscCollection` (`resourcemiscCollection`),
  KEY `resourcemiscPublisher` (`resourcemiscPublisher`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_page` (
  `resourcepageId` int(11) NOT NULL,
  `resourcepagePageStart` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcepagePageEnd` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourcepageId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_summary` (
  `resourcesummaryId` int(11) NOT NULL,
  `resourcesummaryQuotes` int(11) DEFAULT NULL,
  `resourcesummaryParaphrases` int(11) DEFAULT NULL,
  `resourcesummaryMusings` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourcesummaryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_text` (
  `resourcetextId` int(11) NOT NULL,
  `resourcetextAddUserIdNote` int(11) DEFAULT NULL,
  `resourcetextEditUserIdNote` int(11) DEFAULT NULL,
  `resourcetextAddUserIdAbstract` int(11) DEFAULT NULL,
  `resourcetextEditUserIdAbstract` int(11) DEFAULT NULL,
  `resourcetextNote` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcetextAbstract` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcetextUrls` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourcetextUrlText` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourcetextId`),
  FULLTEXT KEY `resourcetextAbstract` (`resourcetextAbstract`),
  FULLTEXT KEY `resourcetextNote` (`resourcetextNote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_timestamp` (
  `resourcetimestampId` int(11) NOT NULL,
  `resourcetimestampTimestamp` datetime DEFAULT current_timestamp(),
  `resourcetimestampTimestampAdd` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`resourcetimestampId`),
  KEY `resourcetimestampTimestampAdd` (`resourcetimestampTimestampAdd`),
  KEY `resourcetimestampTimestamp` (`resourcetimestampTimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_user_tags` (
  `resourceusertagsId` int(11) NOT NULL AUTO_INCREMENT,
  `resourceusertagsTagId` int(11) DEFAULT NULL,
  `resourceusertagsResourceId` int(11) DEFAULT NULL,
  PRIMARY KEY (`resourceusertagsId`),
  KEY `resourceusertagsResourceId` (`resourceusertagsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%resource_year` (
  `resourceyearId` int(11) NOT NULL,
  `resourceyearYear1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `resourceyearYear4` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`resourceyearId`),
  KEY `resourceyearYear1` (`resourceyearYear1`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%statistics_attachment_downloads` (
  `statisticsattachmentdownloadsId` int(11) NOT NULL AUTO_INCREMENT,
  `statisticsattachmentdownloadsResourceId` int(11) NOT NULL,
  `statisticsattachmentdownloadsAttachmentId` int(11) NOT NULL,
  `statisticsattachmentdownloadsCount` int(11) DEFAULT 0,
  `statisticsattachmentdownloadsMonth` int(11) DEFAULT 0,
  PRIMARY KEY (`statisticsattachmentdownloadsId`),
  KEY `statisticsattachmentdownloadsAttachmentId` (`statisticsattachmentdownloadsAttachmentId`),
  KEY `statisticsattachmentdownloadsResourceId` (`statisticsattachmentdownloadsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%statistics_resource_views` (
  `statisticsresourceviewsId` int(11) NOT NULL AUTO_INCREMENT,
  `statisticsresourceviewsResourceId` int(11) NOT NULL,
  `statisticsresourceviewsCount` int(11) DEFAULT 0,
  `statisticsresourceviewsMonth` int(11) DEFAULT 0,
  PRIMARY KEY (`statisticsresourceviewsId`),
  KEY `statisticsresourceviewsResourceId` (`statisticsresourceviewsResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%subcategory` (
  `subcategoryId` int(11) NOT NULL AUTO_INCREMENT,
  `subcategoryCategoryId` int(11) DEFAULT NULL,
  `subcategorySubcategory` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`subcategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%tag` (
  `tagId` int(11) NOT NULL AUTO_INCREMENT,
  `tagTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`tagId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%users` (
  `usersId` int(11) NOT NULL AUTO_INCREMENT,
  `usersUsername` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usersPassword` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usersFullname` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersEmail` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersDepartment` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersInstitution` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersTimestamp` datetime DEFAULT current_timestamp(),
  `usersAdmin` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersCookie` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersPaging` int(11) DEFAULT 20,
  `usersPagingMaxLinks` int(11) DEFAULT 11,
  `usersPagingStyle` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersStringLimit` int(11) DEFAULT 40,
  `usersLanguage` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'auto',
  `usersStyle` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'apa',
  `usersTemplate` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT 'default',
  `usersNotify` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersNotifyAddEdit` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'A',
  `usersNotifyThreshold` int(2) DEFAULT 0,
  `usersNotifyTimestamp` datetime DEFAULT current_timestamp(),
  `usersNotifyDigestThreshold` int(11) DEFAULT 100,
  `usersPagingTagCloud` int(11) DEFAULT 100,
  `usersPasswordQuestion1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordAnswer1` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordQuestion2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordAnswer2` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordQuestion3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersPasswordAnswer3` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersUserSession` longtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersUseBibtexKey` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersUseWikindxKey` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersDisplayBibtexLink` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersDisplayCmsLink` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersCmsTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usersIsCreator` int(11) DEFAULT NULL,
  `usersListlink` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `usersTemplateMenu` int(11) DEFAULT NULL,
  `usersGDPR` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  `usersBlock` varchar(1) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`usersId`),
  UNIQUE KEY `usersUsernameUnique` (`usersUsername`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_bibliography` (
  `userbibliographyId` int(11) NOT NULL AUTO_INCREMENT,
  `userbibliographyUserId` int(11) DEFAULT NULL,
  `userbibliographyUserGroupId` int(11) DEFAULT NULL,
  `userbibliographyTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `userbibliographyDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`userbibliographyId`),
  KEY `userbibliographyTitle` (`userbibliographyTitle`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_bibliography_resource` (
  `userbibliographyresourceId` int(11) NOT NULL AUTO_INCREMENT,
  `userbibliographyresourceBibliographyId` int(11) DEFAULT NULL,
  `userbibliographyresourceResourceId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userbibliographyresourceId`),
  KEY `userbibliographyresourceResourceId` (`userbibliographyresourceResourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_groups` (
  `usergroupsId` int(11) NOT NULL AUTO_INCREMENT,
  `usergroupsTitle` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `usergroupsDescription` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `usergroupsAdminId` int(11) NOT NULL,
  PRIMARY KEY (`usergroupsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_groups_users` (
  `usergroupsusersId` int(11) NOT NULL AUTO_INCREMENT,
  `usergroupsusersGroupId` int(11) DEFAULT NULL,
  `usergroupsusersUserId` int(11) DEFAULT NULL,
  PRIMARY KEY (`usergroupsusersId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_register` (
  `userregisterId` int(11) NOT NULL AUTO_INCREMENT,
  `userregisterHashKey` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `userregisterEmail` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `userregisterTimestamp` datetime DEFAULT  current_timestamp(),
  `userregisterConfirmed` varchar(1) COLLATE utf8mb4_unicode_520_ci DEFAULT 'N',
  `userregisterRequest` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`userregisterId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


CREATE TABLE IF NOT EXISTS `%%WIKINDX_DB_TABLEPREFIX%%user_tags` (
  `usertagsId` int(11) NOT NULL AUTO_INCREMENT,
  `usertagsUserId` int(11) DEFAULT NULL,
  `usertagsTag` varchar(1020) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`usertagsId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
