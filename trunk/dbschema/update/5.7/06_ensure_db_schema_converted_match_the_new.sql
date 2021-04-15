-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Ensure DB schema of converted databases matches that in createSQL.xml
-- 

ALTER TABLE wkx_news MODIFY COLUMN `newsTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wkx_resource_attachments MODIFY COLUMN `resourceattachmentsEmbargoUntil` DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wkx_resource_metadata MODIFY COLUMN `resourcemetadataTimestampEdited` DATETIME DEFAULT NULL;

ALTER TABLE wkx_resource_timestamp MODIFY COLUMN `resourcetimestampTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wkx_resource_timestamp MODIFY COLUMN `resourcetimestampTimestampAdd` DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wkx_users MODIFY COLUMN `usersTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wkx_users MODIFY COLUMN `usersNotifyTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wkx_users MODIFY COLUMN `usersChangePasswordTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wkx_user_register MODIFY COLUMN `userregisterTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
