-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- The old method of creation (XML schema) doesn't support ON UPDATE clause
-- This script fixes db that have been create after 5.2.2
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsTimestamp` datetime DEFAULT current_timestamp();
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_attachments MODIFY COLUMN `resourceattachmentsEmbargoUntil` datetime DEFAULT current_timestamp();

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp MODIFY COLUMN `resourcetimestampTimestamp` datetime DEFAULT current_timestamp();
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%resource_timestamp MODIFY COLUMN `resourcetimestampTimestampAdd` datetime DEFAULT current_timestamp();

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersTimestamp` datetime DEFAULT current_timestamp();
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersNotifyTimestamp` datetime DEFAULT current_timestamp();
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersChangePasswordTimestamp` datetime DEFAULT current_timestamp();
