-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add a missing index on resourceattachmentsTimestamp.

CREATE INDEX `resourceattachmentsTimestamp` ON wkx_resource_attachments (`resourceattachmentsTimestamp`);
