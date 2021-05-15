-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add a default value to users.usersPassword

ALTER TABLE wkx_users MODIFY COLUMN `usersPassword` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
