-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add missing indices and correct some indices (varchar indices needing a prefix to ensure the index is not oversize).
-- Indices that ahve their prefix changed are dropped in UPDATEDATABASE:correctIndices()
-- 
-- https://dev.mysql.com/doc/refman/5.7/en/char.html
-- https://dev.mysql.com/doc/refman/5.7/en/blob.html
-- https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html#data-types-storage-reqs-strings

CREATE INDEX `resourcemetadataResourceId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_metadata (`resourcemetadataResourceId`);
CREATE INDEX `resourcemetadataMetadataId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_metadata (`resourcemetadataMetadataId`);
CREATE INDEX `resourcemetadataAddUserId` ON %%WIKINDX_DB_TABLEPREFIX%%resource_metadata (`resourcemetadataAddUserId`);

CREATE INDEX `categoryCategory` ON %%WIKINDX_DB_TABLEPREFIX%%category (`categoryCategory`(100));
CREATE INDEX `collectionTitle` ON %%WIKINDX_DB_TABLEPREFIX%%collection (`collectionTitle`(100));
CREATE INDEX `configName` ON %%WIKINDX_DB_TABLEPREFIX%%config (`configName`(100));
CREATE INDEX `creatorSurname` ON %%WIKINDX_DB_TABLEPREFIX%%creator (`creatorSurname`(100));
CREATE INDEX `keywordKeyword` ON %%WIKINDX_DB_TABLEPREFIX%%keyword (`keywordKeyword`(100));
CREATE INDEX `publisherName` ON %%WIKINDX_DB_TABLEPREFIX%%publisher (`publisherName`(100));
CREATE INDEX `resourceType` ON %%WIKINDX_DB_TABLEPREFIX%%resource (`resourceType`(100));
CREATE INDEX `resourcecreatorCreatorSurname` ON %%WIKINDX_DB_TABLEPREFIX%%resource_creator (`resourcecreatorCreatorSurname`(100));
CREATE INDEX `resourceyearYear1` ON %%WIKINDX_DB_TABLEPREFIX%%resource_year (`resourceyearYear1`(100));
CREATE INDEX `userbibliographyTitle` ON %%WIKINDX_DB_TABLEPREFIX%%user_bibliography (`userbibliographyTitle`(100));
