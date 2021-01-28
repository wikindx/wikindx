-- 
-- WIKINDX : Bibliographic Management system.
-- @link https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
-- @author The WIKINDX Team
-- @license https://www.isc.org/licenses/ ISC License
--
-- Add a default value to the users.usersFullname field (bugfix #316)

ALTER TABLE %%WIKINDX_DB_TABLEPREFIX%%users MODIFY COLUMN `usersFullname` varchar(1020) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '';
