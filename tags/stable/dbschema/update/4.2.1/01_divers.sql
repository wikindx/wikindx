-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
-- 
-- Modify columns
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTitle` TEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceSubtitle` TEXT DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransTitle` TEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransSubtitle` TEXT DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTitleSort` TEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceTransNoSort` TEXT DEFAULT NULL;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource MODIFY COLUMN `resourceNoSort` TEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersUserSession` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCreators` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataCreators` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheKeywords` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceKeywords` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataKeywords` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheQuoteKeywords` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheParaphraseKeywords` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMusingKeywords` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourcePublishers` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataPublishers` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheConferenceOrganisers` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCollections` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheMetadataCollections` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCollectionTitles` LONGTEXT;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%cache MODIFY COLUMN `cacheResourceCollectionShorts` LONGTEXT;
