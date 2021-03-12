-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Resize collectionType field.
-- Resize publisherType field.
-- Resize resourceattachmentsFileSize field.
-- Resize resourceattachmentsHashFilename field.
-- Resize resourcemetadataType field.
-- Resize resourceType field.

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%collection
MODIFY COLUMN collectionType varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%publisher
MODIFY COLUMN publisherType varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;

UPDATE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments
SET resourceattachmentsFileSize = '0'
WHERE resourceattachmentsFileSize IS NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments
MODIFY COLUMN resourceattachmentsFileSize int(11) NOT NULL DEFAULT 0;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments
MODIFY COLUMN resourceattachmentsHashFilename varchar(40) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_metadata
MODIFY COLUMN resourcemetadataType varchar(2) COLLATE utf8mb4_unicode_520_ci NOT NULL;

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource
MODIFY COLUMN resourceType varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;