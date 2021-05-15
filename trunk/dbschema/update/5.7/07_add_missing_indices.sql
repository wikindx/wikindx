-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add missing indices and correct some indices (varchar indices needing a prefix to ensure the index is not oversize).
-- Indices that have their prefix changed are dropped in UPDATEDATABASE:correctIndices()
-- 
-- https://dev.mysql.com/doc/refman/5.7/en/char.html
-- https://dev.mysql.com/doc/refman/5.7/en/blob.html
-- https://dev.mysql.com/doc/refman/5.7/en/storage-requirements.html#data-types-storage-reqs-strings

CREATE INDEX `resourcemetadataResourceId` ON wkx_resource_metadata (`resourcemetadataResourceId`);
CREATE INDEX `resourcemetadataMetadataId` ON wkx_resource_metadata (`resourcemetadataMetadataId`);
CREATE INDEX `resourcemetadataAddUserId` ON wkx_resource_metadata (`resourcemetadataAddUserId`);

CREATE INDEX `categoryCategory` ON wkx_category (`categoryCategory`(100));
CREATE INDEX `collectionTitle` ON wkx_collection (`collectionTitle`(100));
CREATE INDEX `configName` ON wkx_config (`configName`(100));
CREATE INDEX `creatorSurname` ON wkx_creator (`creatorSurname`(100));
CREATE INDEX `keywordKeyword` ON wkx_keyword (`keywordKeyword`(100));
CREATE INDEX `publisherName` ON wkx_publisher (`publisherName`(100));
CREATE INDEX `resourceType` ON wkx_resource (`resourceType`(100));
CREATE INDEX `resourcecreatorCreatorSurname` ON wkx_resource_creator (`resourcecreatorCreatorSurname`(100));
CREATE INDEX `resourceyearYear1` ON wkx_resource_year (`resourceyearYear1`(100));
CREATE INDEX `userbibliographyTitle` ON wkx_user_bibliography (`userbibliographyTitle`(100));
