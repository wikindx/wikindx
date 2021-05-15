-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Create resourceattachmentsText indexes

ALTER TABLE resource_attachments ADD FULLTEXT `resourceattachmentsText` (`resourceattachmentsText`);
