-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- SQL script of the Wikindx db schema for MySQL
-- 

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

