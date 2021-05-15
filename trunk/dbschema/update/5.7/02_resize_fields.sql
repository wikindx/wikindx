-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Resize all CHAR,` varchar, and` text fields to handle` text encoded with utf8mb4
-- New size = old size x 4
-- text fields become mediumtext fields
-- 
-- https://dev.mysql.com/doc/refman/5.7/en/char.html
-- https://dev.mysql.com/doc/refman/5.7/en/blob.html
-- https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html#data-types-storage-reqs-strings

ALTER TABLE wkx_bibtex_string MODIFY COLUMN `bibtexstringText` mediumtext NOT NULL;

ALTER TABLE wkx_cache MODIFY COLUMN `cacheResourceCreators` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheMetadataCreators` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheResourceKeywords` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheMetadataKeywords` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheQuoteKeywords` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheParaphraseKeywords` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheMusingKeywords` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheResourcePublishers` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheMetadataPublishers` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheConferenceOrganisers` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheResourceCollections` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheMetadataCollections` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheResourceCollectionTitles` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheResourceCollectionShorts` longtext DEFAULT NULL;
ALTER TABLE wkx_cache MODIFY COLUMN `cacheKeywords` longtext DEFAULT NULL;

ALTER TABLE wkx_category MODIFY COLUMN `categoryCategory` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_collection MODIFY COLUMN `collectionTitle` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_collection MODIFY COLUMN `collectionType` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_collection MODIFY COLUMN `collectionTitleShort` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_collection MODIFY COLUMN `collectionDefault` longtext DEFAULT NULL;

ALTER TABLE wkx_config MODIFY COLUMN `configName` varchar(1020) NOT NULL;
ALTER TABLE wkx_config MODIFY COLUMN `configVarchar` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_config MODIFY COLUMN `configText` mediumtext DEFAULT NULL;

ALTER TABLE wkx_creator MODIFY COLUMN `creatorSurname` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_creator MODIFY COLUMN `creatorFirstname` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_creator MODIFY COLUMN `creatorInitials` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_creator MODIFY COLUMN `creatorPrefix` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_custom MODIFY COLUMN `customLabel` varchar(1020) NOT NULL;

ALTER TABLE wkx_database_summary MODIFY COLUMN `databasesummaryDbVersion` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_import_raw MODIFY COLUMN `importrawText` mediumtext NOT NULL;
ALTER TABLE wkx_import_raw MODIFY COLUMN `importrawImportType` varchar(1020) NOT NULL;

ALTER TABLE wkx_keyword MODIFY COLUMN `keywordKeyword` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_keyword MODIFY COLUMN `keywordGlossary` mediumtext DEFAULT NULL;

ALTER TABLE wkx_language MODIFY COLUMN `languageLanguage` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_news MODIFY COLUMN `newsTitle` varchar(1020) NOT NULL;
ALTER TABLE wkx_news MODIFY COLUMN `newsNews` mediumtext DEFAULT NULL;

ALTER TABLE wkx_publisher MODIFY COLUMN `publisherName` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_publisher MODIFY COLUMN `publisherLocation` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_publisher MODIFY COLUMN `publisherType` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_resource MODIFY COLUMN `resourceType` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceTitle` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceSubtitle` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceNoSort` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceIsbn` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField1` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField2` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField3` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField4` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField5` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField6` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField7` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField8` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceField9` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceShortTitle` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceTransTitle` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceTransSubtitle` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceTransShortTitle` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceTransNoSort` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceBibtexKey` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceDoi` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource MODIFY COLUMN `resourceTitleSort` mediumtext DEFAULT NULL;

ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsHashFilename` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsFileName` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsFileType` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsFileSize` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsDescription` mediumtext DEFAULT NULL;

ALTER TABLE wkx_resource_creator MODIFY COLUMN `resourcecreatorCreatorSurname` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_resource_custom MODIFY COLUMN `resourcecustomShort` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_custom MODIFY COLUMN `resourcecustomLong` mediumtext DEFAULT NULL;

ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataPageStart` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataPageEnd` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataParagraph` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataSection` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataChapter` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataType` varchar(1020) NOT NULL;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataText` mediumtext NOT NULL;

ALTER TABLE wkx_resource_page MODIFY COLUMN `resourcepagePageStart` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_page MODIFY COLUMN `resourcepagePageEnd` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_resource_text MODIFY COLUMN `resourcetextNote` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource_text MODIFY COLUMN `resourcetextAbstract` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource_text MODIFY COLUMN `resourcetextUrls` mediumtext DEFAULT NULL;
ALTER TABLE wkx_resource_text MODIFY COLUMN `resourcetextUrlText` mediumtext DEFAULT NULL;

ALTER TABLE wkx_resource_year MODIFY COLUMN `resourceyearYear1` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_year MODIFY COLUMN `resourceyearYear2` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_year MODIFY COLUMN `resourceyearYear3` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_resource_year MODIFY COLUMN `resourceyearYear4` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_statistics MODIFY COLUMN `statisticsStatistics` mediumtext DEFAULT NULL;

ALTER TABLE wkx_subcategory MODIFY COLUMN `subcategorySubcategory` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_tag MODIFY COLUMN `tagTag` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_user_bibliography MODIFY COLUMN `userbibliographyTitle` varchar(1020) NOT NULL;
ALTER TABLE wkx_user_bibliography MODIFY COLUMN `userbibliographyDescription` mediumtext DEFAULT NULL;

ALTER TABLE wkx_user_groups MODIFY COLUMN `usergroupsTitle` varchar(1020) NOT NULL;
ALTER TABLE wkx_user_groups MODIFY COLUMN `usergroupsDescription` mediumtext DEFAULT NULL;

ALTER TABLE wkx_user_register MODIFY COLUMN `userregisterHashKey` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_user_register MODIFY COLUMN `userregisterEmail` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_user_register MODIFY COLUMN `userregisterRequest` mediumtext DEFAULT NULL;

ALTER TABLE wkx_user_tags MODIFY COLUMN `usertagsTag` varchar(1020) DEFAULT NULL;

ALTER TABLE wkx_users MODIFY COLUMN `usersUsername` varchar(1020) NOT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersPassword` varchar(1020) NOT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersFullname` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersEmail` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersLanguage` varchar(1020) DEFAULT 'en';
ALTER TABLE wkx_users MODIFY COLUMN `usersStyle` varchar(1020) DEFAULT 'APA';
ALTER TABLE wkx_users MODIFY COLUMN `usersTemplate` varchar(1020) DEFAULT 'default';
ALTER TABLE wkx_users MODIFY COLUMN `usersPasswordQuestion1` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersPasswordAnswer1` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersPasswordQuestion2` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersPasswordAnswer2` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersPasswordQuestion3` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersPasswordAnswer3` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersUserSession` longtext DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersCmsTag` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersDepartment` varchar(1020) DEFAULT NULL;
ALTER TABLE wkx_users MODIFY COLUMN `usersInstitution` varchar(1020) DEFAULT NULL;
