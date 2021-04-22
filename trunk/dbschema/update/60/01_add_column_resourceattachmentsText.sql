-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add column resourceattachmentsText to resource_attachments

ALTER TABLE resource_attachments ADD COLUMN `resourceattachmentsText` mediumtext COLLATE utf8mb4_unicode_520_ci DEFAULT NULL;
