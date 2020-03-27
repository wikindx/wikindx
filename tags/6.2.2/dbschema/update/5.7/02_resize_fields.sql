-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Resize all CHAR,` varchar, and` text fields to handle` text encoded with utf8mb4
-- New size = old size x 4
-- text fields become mediumtext fields
-- 
-- https://dev.mysql.com/doc/refman/5.7/en/char.html
-- https://dev.mysql.com/doc/refman/5.7/en/blob.html
-- https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html#data-types-storage-reqs-strings

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bibtex_string MODIFY COLUMN `bibtexstringText` mediumtext NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCreators` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataCreators` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceKeywords` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataKeywords` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheQuoteKeywords` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheParaphraseKeywords` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMusingKeywords` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourcePublishers` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataPublishers` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheConferenceOrganisers` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCollections` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataCollections` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCollectionTitles` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCollectionShorts` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheKeywords` longtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category MODIFY COLUMN `categoryCategory` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection MODIFY COLUMN `collectionTitle` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection MODIFY COLUMN `collectionType` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection MODIFY COLUMN `collectionTitleShort` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection MODIFY COLUMN `collectionDefault` longtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config MODIFY COLUMN `configName` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config MODIFY COLUMN `configVarchar` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config MODIFY COLUMN `configText` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator MODIFY COLUMN `creatorSurname` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator MODIFY COLUMN `creatorFirstname` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator MODIFY COLUMN `creatorInitials` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator MODIFY COLUMN `creatorPrefix` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom MODIFY COLUMN `customLabel` varchar(1020) NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary MODIFY COLUMN `databasesummaryDbVersion` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw MODIFY COLUMN `importrawText` mediumtext NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw MODIFY COLUMN `importrawImportType` varchar(1020) NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword MODIFY COLUMN `keywordKeyword` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword MODIFY COLUMN `keywordGlossary` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%language MODIFY COLUMN `languageLanguage` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news MODIFY COLUMN `newsTitle` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news MODIFY COLUMN `newsNews` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher MODIFY COLUMN `publisherName` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher MODIFY COLUMN `publisherLocation` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher MODIFY COLUMN `publisherType` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceType` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTitle` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceSubtitle` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceNoSort` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceIsbn` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField1` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField2` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField3` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField4` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField5` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField6` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField7` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField8` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceField9` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceShortTitle` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransTitle` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransSubtitle` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransShortTitle` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransNoSort` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceBibtexKey` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceDoi` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTitleSort` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsHashFilename` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsFileName` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsFileType` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsFileSize` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsDescription` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator MODIFY COLUMN `resourcecreatorCreatorSurname` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom MODIFY COLUMN `resourcecustomShort` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom MODIFY COLUMN `resourcecustomLong` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataPageStart` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataPageEnd` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataParagraph` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataSection` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataChapter` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataType` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata MODIFY COLUMN `resourcemetadataText` mediumtext NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page MODIFY COLUMN `resourcepagePageStart` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page MODIFY COLUMN `resourcepagePageEnd` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text MODIFY COLUMN `resourcetextNote` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text MODIFY COLUMN `resourcetextAbstract` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text MODIFY COLUMN `resourcetextUrls` mediumtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text MODIFY COLUMN `resourcetextUrlText` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year MODIFY COLUMN `resourceyearYear1` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year MODIFY COLUMN `resourceyearYear2` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year MODIFY COLUMN `resourceyearYear3` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year MODIFY COLUMN `resourceyearYear4` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics MODIFY COLUMN `statisticsStatistics` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory MODIFY COLUMN `subcategorySubcategory` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%tag MODIFY COLUMN `tagTag` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography MODIFY COLUMN `userbibliographyTitle` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography MODIFY COLUMN `userbibliographyDescription` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups MODIFY COLUMN `usergroupsTitle` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups MODIFY COLUMN `usergroupsDescription` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register MODIFY COLUMN `userregisterHashKey` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register MODIFY COLUMN `userregisterEmail` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register MODIFY COLUMN `userregisterRequest` mediumtext DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags MODIFY COLUMN `usertagsTag` varchar(1020) DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersUsername` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPassword` varchar(1020) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersFullname` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersEmail` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersLanguage` varchar(1020) DEFAULT 'en';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersStyle` varchar(1020) DEFAULT 'APA';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersTemplate` varchar(1020) DEFAULT 'default';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPasswordQuestion1` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPasswordAnswer1` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPasswordQuestion2` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPasswordAnswer2` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPasswordQuestion3` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersPasswordAnswer3` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersUserSession` longtext DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersCmsTag` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersDepartment` varchar(1020) DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersInstitution` varchar(1020) DEFAULT NULL;
