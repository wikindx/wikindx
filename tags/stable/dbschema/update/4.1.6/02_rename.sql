-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Rename fields in tables for v4.0 upgrade
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bibtex_string CHANGE `id` bibtexstringId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%bibtex_string CHANGE `text` bibtexstringText text NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `resourceCreators` cacheResourceCreators text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `metadataCreators` cacheMetadataCreators text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `resourceKeywords` cacheResourceKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `metadataKeywords` cacheMetadataKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `quoteKeywords` cacheQuoteKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `paraphraseKeywords` cacheParaphraseKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `musingKeywords` cacheMusingKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `resourcePublishers` cacheResourcePublishers text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `metadataPublishers` cacheMetadataPublishers text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `conferenceOrganisers` cacheConferenceOrganisers text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `resourceCollections` cacheResourceCollections text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `metadataCollections` cacheMetadataCollections text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `resourceCollectionTitles` cacheResourceCollectionTitles text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache CHANGE `resourceCollectionShorts` cacheResourceCollectionShorts text;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category CHANGE `id` categoryId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%category CHANGE `category` categoryCategory varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection CHANGE id collectionId int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `title` configTitle varchar(255) DEFAULT 'WIKINDX';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `contactEmail` configContactEmail varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `description` configDescription text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `fileDeleteSeconds` configFileDeleteSeconds int(11) DEFAULT '3600';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `paging` configPaging int(11) DEFAULT '20';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `pagingMaxLinks` configPagingMaxLinks int(11) DEFAULT '11';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `stringLimit` configStringLimit int(11) DEFAULT '40';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `language` configLanguage varchar(10) DEFAULT 'en';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `style` configStyle varchar(255) DEFAULT 'MHRA';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `template` configTemplate varchar(255) DEFAULT 'default';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `multiUser` configMultiUser enum('N','Y') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `userRegistration` configUserRegistration enum('N','Y') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `notify` configNotify enum('Y','N') DEFAULT 'Y';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `imgWidthLimit` configImgWidthLimit int(11) DEFAULT '400';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `imgHeightLimit` configImgHeightLimit int(11) DEFAULT '400';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `fileAttach` configFileAttach enum('N','Y') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `fileViewLoggedOnOnly` configFileViewLoggedOnOnly enum('N','Y') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `maxPaste` configMaxPaste int(11) DEFAULT '10';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `lastChanges` configLastChanges int(11) DEFAULT '10';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `pagingTagCloud` configPagingTagCloud int(11) DEFAULT '100';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `lastChangesType` configLastChangesType char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `importBib` configImportBib char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `lastChangesDayLimit` configLastChangesDayLimit int(11) DEFAULT '10';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `emailNews` configEmailNews char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `emailNewRegistrations` configEmailNewRegistrations varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `registrationModerate` configRegistrationModerate char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `quarantine` configQuarantine varchar(1) NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `noSort` configNoSort mediumtext;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `searchFilter` configSearchFilter mediumtext;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `listlink` configListlink varchar(1) NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `emailStatistics` configEmailStatistics varchar(1) NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `statisticsCompiled` configStatisticsCompiled datetime DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `metadataAllow` configMetadataAllow varchar(1) DEFAULT 'Y';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `metadataUserOnly` configMetadataUserOnly varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `denyReadOnly` configDenyReadOnly varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `readOnlyAccess` configReadOnlyAccess varchar(1) DEFAULT 'Y';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `originatorEditOnly` configOriginatorEditOnly varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `globalEdit` configGlobalEdit varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `errorReport` configErrorReport varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `printSql` configPrintSql varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `captchaPublicKey` configCaptchaPublicKey varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%config CHANGE `captchaPrivateKey` configCaptchaPrivateKey varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator CHANGE `id` creatorId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator CHANGE `surname` creatorSurname varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator CHANGE `firstname` creatorFirstname varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator CHANGE `initials` creatorInitials varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%creator CHANGE `prefix` creatorPrefix varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom CHANGE id customId int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom CHANGE custom_label customLabel varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%custom CHANGE custom_size customSize varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary CHANGE `totalResources` databasesummaryTotalResources int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary CHANGE `totalQuotes` databasesummaryTotalQuotes int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary CHANGE `totalParaphrases` databasesummaryTotalParaphrases int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary CHANGE `totalMusings` databasesummaryTotalMusings int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%database_summary CHANGE `dbVersion` databasesummaryDbVersion varchar(10);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw CHANGE `id` importrawId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw CHANGE `stringId` importrawStringId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw CHANGE `text` importrawText text NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%import_raw CHANGE `importType` importrawImportType varchar(255) NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword CHANGE `id` keywordId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%keyword CHANGE `keyword` keywordKeyword varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news CHANGE `id` newsId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news CHANGE `title` newsTitle varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news CHANGE `news` newsNews text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news CHANGE `timestamp` newsTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%news CHANGE `emailSent` newsEmailSent char(1) DEFAULT 'N';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%papers CHANGE `id` papersId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%papers CHANGE `hashFilename` papersHashFilename varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%papers CHANGE `userId` papersUserId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%papers CHANGE `filename` papersFilename varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%papers CHANGE `timestamp` papersTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher CHANGE id publisherId int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `id` resourceId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `type` resourceType varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `title` resourceTitle varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `subtitle` resourceSubtitle varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `noSort` resourceNoSort varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `isbn` resourceIsbn varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field1` resourceField1 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field2` resourceField2 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field3` resourceField3 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field4` resourceField4 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field5` resourceField5 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field6` resourceField6 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field7` resourceField7 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field8` resourceField8 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `field9` resourceField9 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `shortTitle` resourceShortTitle varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `transTitle` resourceTransTitle varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `transSubtitle` resourceTransSubtitle varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `transShortTitle` resourceTransShortTitle varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `transNoSort` resourceTransNoSort varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `bibtexKey` resourceBibtexKey varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource CHANGE `doi` resourceDoi varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `id` resourceattachmentsId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `hashFilename` resourceattachmentsHashFilename varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `resourceId` resourceattachmentsResourceId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `filename` resourceattachmentsFilename varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `fileType` resourceattachmentsFileType varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `fileSize` resourceattachmentsFileSize varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `downloads` resourceattachmentsDownloads int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `downloadsPeriod` resourceattachmentsDownloadsPeriod int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `primary` resourceattachmentsPrimary varchar(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments CHANGE `timestamp` resourceattachmentsTimestamp datetime DEFAULT '0000-00-00 00:00:00';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_category CHANGE `id` resourcecategoryId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_category CHANGE `categories` resourcecategoryCategories varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator CHANGE `id` resourcecreatorId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator CHANGE `creator1` resourcecreatorCreator1 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator CHANGE `creator2` resourcecreatorCreator2 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator CHANGE `creator3` resourcecreatorCreator3 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator CHANGE `creator4` resourcecreatorCreator4 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_creator CHANGE `creator5` resourcecreatorCreator5 varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE id resourcecustomId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE customId resourcecustomCustomId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE resourceId resourcecustomResourceId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE custom_short resourcecustomShort varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE custom_long resourcecustomLong text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE addUserIdCustom resourcecustomAddUserIdCustom int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_custom CHANGE editUserIdCustom resourcecustomEditUserIdCustom int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_keyword CHANGE `id` resourcekeywordId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_keyword CHANGE `keywords` resourcekeywordKeywords text;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE id resourcemiscId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE collection resourcemiscCollection int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE publisher resourcemiscPublisher int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE tag resourcemiscTag int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE editUserIdResource resourcemiscEditUserIdResource int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE addUserIdResource resourcemiscAddUserIdResource int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE miscField1 resourcemiscField1 int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE miscField2 resourcemiscField2 int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE miscField3 resourcemiscField3 int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE miscField4 resourcemiscField4 int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE miscField5 resourcemiscField5 int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE miscField6 resourcemiscField6 int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE accesses resourcemiscAccesses int(11) DEFAULT '1';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE attachDownloads resourcemiscAttachDownloads int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE maturityIndex resourcemiscMaturityIndex double;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE peerReviewed resourcemiscPeerReviewed char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE quarantine resourcemiscQuarantine varchar(1) NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_misc CHANGE accessesPeriod resourcemiscAccessesPeriod int(11) NOT NULL DEFAULT '1';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE id resourcemusingId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE resourceId resourcemusingResourceId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE page_start resourcemusingPageStart varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE page_end resourcemusingPageEnd varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE musing_keywords resourcemusingKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE paragraph resourcemusingParagraph varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE section resourcemusingSection varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing CHANGE chapter resourcemusingChapter varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text CHANGE id resourcemusingtextId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text CHANGE text resourcemusingtextText text NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text CHANGE addUserIdMusing resourcemusingtextAddUserIdMusing int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text CHANGE timestamp resourcemusingtextTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_musing_text CHANGE musingPrivate resourcemusingtextPrivate char(1) DEFAULT 'N';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page CHANGE `id` resourcepageId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page CHANGE `pageStart` resourcepagePageStart varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_page CHANGE `pageEnd` resourcepagePageEnd varchar(10);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE id resourceparaphraseId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE resourceId resourceparaphraseResourceId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE page_start resourceparaphrasePageStart varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE page_end resourceparaphrasePageEnd varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE paraphrase_keywords resourceparaphraseKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE paragraph resourceparaphraseParagraph varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE section resourceparaphraseSection varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase CHANGE chapter resourceparaphraseChapter varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment CHANGE id resourceparaphrasecommentId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment CHANGE paraphraseId resourceparaphrasecommentParaphraseId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment CHANGE addUserIdParaphrase resourceparaphrasecommentAddUserIdParaphrase int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment CHANGE comment resourceparaphrasecommentComment text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment CHANGE timestamp resourceparaphrasecommentTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_comment CHANGE paraphrasePrivate resourceparaphrasecommentPrivate char(1) DEFAULT 'N';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_text CHANGE `id` resourceparaphrasetextId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_text CHANGE `text` resourceparaphrasetextText text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_paraphrase_text CHANGE `addUserIdParaphrase` resourceparaphrasetextAddUserIdParaphrase int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE id resourcequoteId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE resourceId resourcequoteResourceId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE page_start resourcequotePageStart varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE page_end resourcequotePageEnd varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE quote_keywords resourcequoteKeywords text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE paragraph resourcequoteParagraph varchar(10);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE section resourcequoteSection varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote CHANGE chapter resourcequoteChapter varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment CHANGE id resourcequotecommentId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment CHANGE quoteId resourcequotecommentQuoteId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment CHANGE addUserIdQuote resourcequotecommentAddUserIdQuote int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment CHANGE comment resourcequotecommentComment text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment CHANGE timestamp resourcequotecommentTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_comment CHANGE quotePrivate resourcequotecommentPrivate char(1) DEFAULT 'N';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_text CHANGE `id` resourcequotetextId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_text CHANGE `text` resourcequotetextText text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_quote_text CHANGE `addUserIdQuote` resourcequotetextAddUserIdQuote int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary CHANGE `id` resourcesummaryId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary CHANGE `quotes` resourcesummaryQuotes int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary CHANGE `paraphrases` resourcesummaryParaphrases int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_summary CHANGE `musings` resourcesummaryMusings int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `id` resourcetextId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `note` resourcetextNote text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `abstract` resourcetextAbstract text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `urls` resourcetextUrls text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `urlText` resourcetextUrlText text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `editUserIdNote` resourcetextEditUserIdNote int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `addUserIdNote` resourcetextAddUserIdNote int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `editUserIdAbstract` resourcetextEditUserIdAbstract int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_text CHANGE `addUserIdAbstract` resourcetextAddUserIdAbstract int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp CHANGE `id` resourcetimestampId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp CHANGE `timestamp` resourcetimestampTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp CHANGE `timestampAdd` resourcetimestampTimestampAdd datetime NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags CHANGE `id` resourceusertagsId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags CHANGE `tagId` resourceusertagsTagId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_user_tags CHANGE `resourceId` resourceusertagsResourceId int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year CHANGE `id` resourceyearId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year CHANGE `year1` resourceyearYear1 varchar(30);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year CHANGE `year2` resourceyearYear2 varchar(30);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year CHANGE `year3` resourceyearYear3 varchar(30);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_year CHANGE `year4` resourceyearYear4 varchar(30);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics CHANGE `id` statisticsId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics CHANGE `resourceId` statisticsResourceId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics CHANGE `attachmentId` statisticsAttachmentId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%statistics CHANGE `statistics` statisticsStatistics text;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory CHANGE `id` subcategoryId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory CHANGE `categoryId` subcategoryCategoryId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%subcategory CHANGE `subcategory` subcategorySubcategory varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%tag CHANGE `id` tagId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%tag CHANGE `tag` tagTag varchar(255);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `id` usersId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `username` usersUsername varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `password` usersPassword varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `fullname` usersFullname varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `email` usersEmail varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `admin` usersAdmin enum('N','Y') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `cookie` usersCookie enum('N','Y') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `paging` usersPaging int(11) DEFAULT '20';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `pagingMaxLinks` usersPagingMaxLinks int(11) DEFAULT '11';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `stringLimit` usersStringLimit int(11) DEFAULT '40';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `language` usersLanguage varchar(10) DEFAULT 'en';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `style` usersStyle varchar(255) DEFAULT 'APA';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `template` usersTemplate varchar(255) DEFAULT 'default';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `notify` usersNotify enum('N','A','M') DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `notifyAddEdit` usersNotifyAddEdit char(1) DEFAULT 'A';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `pagingTagCloud` usersPagingTagCloud int(11) DEFAULT '100';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `passwordQuestion1` usersPasswordQuestion1 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `passwordAnswer1` usersPasswordAnswer1 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `passwordQuestion2` usersPasswordQuestion2 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `passwordAnswer2` usersPasswordAnswer2 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `passwordQuestion3` usersPasswordQuestion3 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `passwordAnswer3` usersPasswordAnswer3 varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `useBibtexKey` usersUseBibtexKey char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `userSession` usersUserSession text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `useWikindxKey` usersUseWikindxKey char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `pagingStyle` usersPagingStyle char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `displayBibtexLink` usersDisplayBibtexLink char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `displayCmsLink` usersDisplayCmsLink char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `cmsTag` usersCmsTag varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `timestamp` usersTimestamp datetime NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `notifyThreshold` usersNotifyThreshold int(2);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users CHANGE `notifyTimestamp` usersNotifyTimestamp datetime NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography CHANGE `id` userbibliographyId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography CHANGE `userId` userbibliographyUserId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography CHANGE `title` userbibliographyTitle varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography CHANGE `description` userbibliographyDescription text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography CHANGE `bibliography` userbibliographyBibliography text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_bibliography CHANGE `userGroupId` userbibliographyUserGroupId int(11);

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups CHANGE `id` usergroupsId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups CHANGE `title` usergroupsTitle varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups CHANGE `description` usergroupsDescription text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups CHANGE `adminId` usergroupsAdminId int(11) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups CHANGE `userIds` usergroupsUserIds text;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_groups CHANGE `bibliographyIds` usergroupsBibliographyIds text;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register CHANGE `id` userregisterId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register CHANGE `hashKey` userregisterHashKey varchar(255) NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register CHANGE `email` userregisterEmail varchar(255);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register CHANGE `timestamp` userregisterTimestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register CHANGE `confirmed` userregisterConfirmed char(1) DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_register CHANGE `request` userregisterRequest text;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags CHANGE `id` usertagsId int(11) NOT NULL AUTO_INCREMENT NOT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags CHANGE `userId` usertagsUserId int(11);
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%user_tags CHANGE `tag` usertagsTag varchar(255);

