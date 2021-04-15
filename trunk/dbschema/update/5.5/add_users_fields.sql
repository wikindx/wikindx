-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
-- 
-- Add three fields to handle GDPR and improve auth security
-- 

ALTER TABLE wkx_users ADD `usersChangePasswordTimestamp` DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wkx_users ADD `usersGDPR` VARCHAR(1) NOT NULL DEFAULT 'N';
ALTER TABLE wkx_users ADD `usersBlock` VARCHAR(1) NOT NULL DEFAULT 'N';

-- Update all current users usersChangePasswordTimestamp fields to their current usersTimestamp fields
UPDATE wkx_users
SET usersChangePasswordTimestamp = usersTimestamp;
