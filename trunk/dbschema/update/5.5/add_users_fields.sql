-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add three fields to handle GDPR and improve auth security
-- 

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD `usersChangePasswordTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD `usersGDPR` VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users ADD `usersBlock` VARCHAR(1) NOT NULL DEFAULT 'N';

-- Update all current users usersChangePasswordTimestamp fields to their current usersTimestamp fields
UPDATE %%WIKINDX_DB_TABLEPREFIX%%users
SET usersChangePasswordTimestamp = usersTimestamp;
